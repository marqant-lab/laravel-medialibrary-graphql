<?php

namespace Marqant\LaravelMediaLibraryGraphQL\GraphQL\Mutations;

use \Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pipeline\Pipeline;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use \GraphQL\Type\Definition\ResolveInfo;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Marqant\LaravelMediaLibraryGraphQL\Resources\MediaResource;

/**
 * Class UploadFile
 *
 * @package Marqant\LaravelMediaLibraryGraphQL\GraphQL\Mutations
 */
class UploadFile
{
    /**
     * Upload file to the Model from config
     *
     * @param null           $rootValue   Usually contains the result returned from the parent field.
     *                                    In this case, it is always `null`.
     * @param mixed[]        $args        The arguments that were passed into the field.
     * @param GraphQLContext $context     Arbitrary data that is shared between all fields of a single query.
     * @param ResolveInfo    $resolveInfo Information about the query itself, such as the execution state,
     *                                    the field name, path to the field from the root, and more.
     *
     * @return array
     *
     * @throws Exception
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        // validation
        $validator = Validator::make($args, config('laravel-medialibrary-graphql.validation_rules.upload'));

        if ($validator->fails()) {
            $namespace = 'Marqant\LaravelMediaLibraryGraphQL\GraphQL\Mutations\UploadFile';
            \Log::error("$namespace validation errors: \n" .
                print_r($validator->errors(), true) .
                "\n  params: " . print_r($args, true));

            $message = "Trying to upload not valid file. Please try another one or contact support to check logs.";
            throw new Exception(__($message));
        }

        $model_key   = $args['model'] ?? 'default';
        $model_class = config("laravel-medialibrary-graphql.models.$model_key");
        $Model       = app($model_class);

        try {
            /** @var HasMedia $FileOwner */
            $FileOwner = $Model->findOrFail($args['id']);
        } catch (Exception $exception) {
            throw new Exception(__("Can't find Model by ID: ") . $args['id']);
        }

        try {
            $request = new Request();

            /** @var UploadedFile $File */
            $File = $args['file'];

            $name = $args['name'] ?? pathinfo($File->getClientOriginalName(), PATHINFO_FILENAME);

            // pipelines data
            $pipelines_data = [
                'action' => 'uploaded file',
                'owner'  => $FileOwner,
                'model'  => $model_class,
                'file'   => $File,
                'name'   => $name,
                'props'  => $args['properties'] ?? [],
            ];

            // execute pipelines and save file after
            $NewMedia = app(Pipeline::class)
                ->send($pipelines_data)
                ->through(config('laravel-medialibrary-graphql.pipelines.uploaded'))
                ->then(
                    function () use ($FileOwner, $File, $name, $args) {
                        $NewMedia = $FileOwner->addMedia($File)
                            ->usingName($name)
                            ->withCustomProperties($args['properties'] ?? [])
                            ->toMediaCollection(config('laravel-medialibrary-graphql.def_media_collection'));

                        return $NewMedia;
                    }
                );

            // after created new Media pipes
            app(Pipeline::class)
                ->send([
                    'action' => 'created new Media',
                    'owner'  => $FileOwner,
                    'model'  => $model_class,
                    'media'  => $NewMedia,
                ])
                ->through(config('laravel-medialibrary-graphql.pipelines.created_new_media'))
                ->thenReturn();

            /** @var Media[]|Collection $Medias */
            $Medias = $FileOwner->getMedia(config('laravel-medialibrary-graphql.def_media_collection'));

            return MediaResource::collection($Medias)->toArray($request);
        } catch (Exception $exception) {
            \Log::error('Marqant\LaravelMediaLibraryGraphQL\GraphQL\Mutations\UploadFile ERROR ' .
                "\n  params: " . print_r($args, true) .
                "\n  error: " . $exception->getMessage() .
                "\n  at: " . $exception->getFile() .
                "\n line: " . $exception->getLine());

            throw new Exception(__("Something went wrong! Please contact support."));
        }
    }
}
