<?php

namespace Marqant\LaravelMediaLibraryGraphQL\GraphQL\Mutations;

use \Exception;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Validator;
use \GraphQL\Type\Definition\ResolveInfo;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class DownloadMedia
 *
 * @package Marqant\LaravelMediaLibraryGraphQL\GraphQL\Mutations
 */
class DownloadMedia
{
    /**
     * Download file by uuid
     *
     * @param null           $rootValue   Usually contains the result returned from the parent field.
     *                                    In this case, it is always `null`.
     * @param mixed[]        $args        The arguments that were passed into the field.
     * @param GraphQLContext $context     Arbitrary data that is shared between all fields of a single query.
     * @param ResolveInfo    $resolveInfo Information about the query itself, such as the execution state,
     *                                    the field name, path to the field from the root, and more.
     *
     * @return string
     *
     * @throws Exception
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        // validation
        $validator = Validator::make($args, config('laravel-medialibrary-graphql.validation_rules.download'));

        if ($validator->fails()) {
            \Log::error("Marqant\LaravelMediaLibraryGraphQL\GraphQL\Mutations\DownloadMedia validation errors: \n" .
                print_r($validator->errors(), true) .
                "\n  params: " . print_r($args, true));

            throw new Exception(__("Empty or wrong param(s)."));
        }

        try {
            /** @var Media $Media */
            $Media = Media::query()
                ->where('uuid', $args['uuid'])
                ->firstOrFail();
        } catch (Exception $exception) {
            throw new Exception(__("Can't find Media file by UUID: ") . "'{$args['uuid']}'!");
        }

        // get file owner
        $FileOwner = $Media->model()
            ->get()->first();

        // pipelines data
        $pipelines_data = [
            'action' => 'downloaded file',
            'media'  => $Media,
            'owner'  => $FileOwner,
        ];

        // execute pipelines and get base64 file string after
        $file_base64 = app(Pipeline::class)
            ->send($pipelines_data)
            ->through(config('laravel-medialibrary-graphql.pipelines.downloaded'))
            ->then(
                function () use ($Media) {
                    return base64_encode(stream_get_contents($Media->stream()));
                }
            );

        return $file_base64;
    }
}
