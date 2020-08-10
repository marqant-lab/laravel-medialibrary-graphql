<?php

namespace Marqant\LaravelMediaLibraryGraphQL\GraphQL\Mutations;

use \Exception;
use Illuminate\Pipeline\Pipeline;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\Validator;
use \GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class DeleteAllMedia
 *
 * @package Marqant\LaravelMediaLibraryGraphQL\GraphQL\Mutations
 */
class DeleteAllMedia
{
    /**
     * Delete all files for the Model from config
     *
     * @param null           $rootValue   Usually contains the result returned from the parent field.
     *                                    In this case, it is always `null`.
     * @param mixed[]        $args        The arguments that were passed into the field.
     * @param GraphQLContext $context     Arbitrary data that is shared between all fields of a single query.
     * @param ResolveInfo    $resolveInfo Information about the query itself, such as the execution state,
     *                                    the field name, path to the field from the root, and more.
     *
     * @return void
     *
     * @throws Exception
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        // validation
        $validator = Validator::make($args, config('laravel-medialibrary-graphql.validation_rules.delete_all'));

        if ($validator->fails()) {
            \Log::error("Marqant\LaravelMediaLibraryGraphQL\GraphQL\Mutations\DeleteMedia validation errors: \n" .
                print_r($validator->errors(), true) .
                "\n  params: " . print_r($args, true));

            throw new Exception(__("Empty or wrong param(s)."));
        }

        $Model = app(config('laravel-medialibrary-graphql.models.main'));

        try {
            /** @var HasMedia $FileOwner */
            $FileOwner = $Model->findOrFail($args['id']);
        } catch (Exception $exception) {
            throw new Exception(__("Can't find Model by ID: ") . "{$args['id']}.");
        }

        // pipelines data
        $pipelines_data = [
            'action' => 'deleted all media files',
            'owner'  => $FileOwner,
        ];

        // execute pipelines and save file after
        app(Pipeline::class)
            ->send($pipelines_data)
            ->through(config('laravel-medialibrary-graphql.pipelines.deleted_all'))
            ->then(
                function () use ($FileOwner) {
                    $FileOwner->clearMediaCollection(config('laravel-medialibrary-graphql.def_media_collection'));
                }
            );

        return;
    }
}
