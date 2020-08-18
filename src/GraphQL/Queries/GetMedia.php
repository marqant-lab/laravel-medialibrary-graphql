<?php

namespace Marqant\LaravelMediaLibraryGraphQL\GraphQL\Queries;

use \Exception;
use \Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Collection;
use \GraphQL\Type\Definition\ResolveInfo;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Marqant\LaravelMediaLibraryGraphQL\Resources\MediaResource;

/**
 * Class GetMedia
 *
 * @package Marqant\LaravelMediaLibraryGraphQL\GraphQL\Queries
 */
class GetMedia
{
    /**
     * Return
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
        $request     = new Request();
        $model_key   = $args['model'] ?? 'default';
        $model_class = config("laravel-medialibrary-graphql.models.$model_key");
        $Model       = app($model_class);

        try {
            /** @var HasMedia $FileOwner */
            $FileOwner = $Model->findOrFail($args['id']);
        } catch (Exception $exception) {
            throw new Exception(__("Can't find Model by ID: {$args['id']}."));
        }

        // pipelines data
        $pipelines_data = [
            'action' => 'got files list',
            'owner'  => $FileOwner,
            'model'  => $model_class,
        ];

        // execute pipelines
        app(Pipeline::class)
            ->send($pipelines_data)
            ->through(config('laravel-medialibrary-graphql.pipelines.got_list'))
            ->then(
                function () {
                    //
                }
            );

        /** @var Media[]|Collection $Medias */
        $Medias = $FileOwner->getMedia(config('laravel-medialibrary-graphql.def_media_collection'));

        return MediaResource::collection($Medias)->toArray($request);
    }
}
