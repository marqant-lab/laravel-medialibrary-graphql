<?php

namespace Marqant\LaravelMediaLibraryGraphQL\Tests\GraphQL\Queries;

use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

/**
 * Class GraphQLMediaLibrary
 *
 * @package Marqant\LaravelMediaLibraryGraphQL\Tests\GraphQL\Queries
 */
class GetMediaTest extends TestCase
{
    use MakesGraphQLRequests;

    /**
     * @group GraphQLMediaLibrary
     *
     * @test
     */
    public function testGetOwnerMediaFilesList()
    {
        // create a User
        $User = factory(config('auth.providers.users.model'))->create();
        // authenticate the User
        Sanctum::actingAs(
            $User,
            ['*']
        );

        if (config('auth.providers.users.model') == config('laravel-medialibrary-graphql.models.main')) {
            $Owner = $User;
        } else {
            $Owner = factory(config('laravel-medialibrary-graphql.models.main'))->create();
        }

        Storage::fake('public');
        $Owner->addMedia(UploadedFile::fake()->create('some-file.pdf', 1024))
            ->usingName('PDF file')
            ->withCustomProperties([
                "title" => "test title",
                "description" => "test description",
            ])
            ->toMediaCollection(config('laravel-medialibrary-graphql.def_media_collection'));

        // execute GraphQL query 'getMedia'
        $getMediaResponse = $this->postGraphQL([
            "query" => 'query GetMedia($id: Int!) {
                    getMedia(id: $id) {
                        id
                        name
                        fileName
                        path
                        url
                        downloadUrl
                        properties
                        type
                        uuid
                        createdAt
                        updatedAt
                    }
                }',
            "variables" => [
                "id" => $Owner->id
            ]
        ], [
            'Authorization' => 'Bearer ' . $User->createToken($User->id)->plainTextToken,
        ]);

        // check response
        $getMediaResponse
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'getMedia' => [
                        '*' => [
                            "id",
                            "name",
                            "fileName",
                            "path",
                            "url",
                            "downloadUrl",
                            "properties",
                            "type",
                            "uuid",
                            "createdAt",
                            "updatedAt",
                        ]
                    ],
                ],
            ]);
    }
}
