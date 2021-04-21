<?php

namespace Marqant\LaravelMediaLibraryGraphQL\GraphQL\Mutations;

use \Exception;
use \GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Marqant\LaravelMediaLibraryGraphQL\Facades\MediaLibrary;

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
        MediaLibrary::validate($args, 'delete_all');

        // delete all owner Media
        MediaLibrary::deleteAllMedia($args);

        return;
    }
}
