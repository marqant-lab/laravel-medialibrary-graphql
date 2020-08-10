<?php

namespace Marqant\LaravelMediaLibraryGraphQL\Tests\GraphQL\Mutations;

use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\UploadedFile;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

/**
 * Class GraphQLMediaLibrary
 *
 * @package Marqant\LaravelMediaLibraryGraphQL\Tests\GraphQL\Mutations
 */
class UploadFileTest extends TestCase
{
    use MakesGraphQLRequests;

    /**
     * @group GraphQLMediaLibrary
     *
     * @test
     */
    public function testUploadMediaFileToOwner()
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

        // execute GraphQL mutation 'uploadFile'
        $uploadFileResponse = $this->multipartGraphQL(
            [
                'operations' => /** @lang JSON */ '{
                    "query": "mutation UploadFile($id:Int!,$file:Upload!,$name:String,$properties:Json){uploadFile(id:$id,file:$file,name:$name,properties:$properties) {downloadUrl}}",
                    "variables": {
                        "id": ' . $Owner->id . ',
                        "name": "PDF file",
                        "properties": {
                            "title": "test title",
                            "description": "test description"
                        },
                        "file": null
                    }
                }',
                'map' => /** @lang JSON */ '{
                    "0": ["variables.file"]
                }',
            ], [
                '0' => UploadedFile::fake()->create('some-file.pdf', 1024),
            ], [
                'Authorization' => 'Bearer ' . $User->createToken($User->id)->plainTextToken,
            ]
        );

        $uploadFileResponse->assertOk();

        $Medias = $Owner->getMedia(config('laravel-medialibrary-graphql.def_media_collection'));
        $this->assertNotEmpty($Medias);
        $this->assertCount(1, $Medias);
    }
}
