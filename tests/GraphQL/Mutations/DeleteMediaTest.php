<?php

namespace Marqant\LaravelMediaLibraryGraphQL\Tests\GraphQL\Mutations;

use Spatie\MediaLibrary\HasMedia;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Class DeleteMediaTest
 *
 * @package Marqant\LaravelMediaLibraryGraphQL\Tests\GraphQL\Mutations
 */
class DeleteMediaTest extends TestCase
{
    use MakesGraphQLRequests;

    /**
     * @group GraphQLMediaLibrary
     *
     * @test
     */
    public function testDeleteOwnerMediaFile()
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
        // create two files and assign them to the Owner
        $Owner->addMedia(UploadedFile::fake()->create('some-file.pdf', 1024))
            ->usingName('PDF file')
            ->withCustomProperties([
                "title" => "test title",
                "description" => "test description",
            ])
            ->toMediaCollection(config('laravel-medialibrary-graphql.def_media_collection'));
        $Owner->addMedia(UploadedFile::fake()->create('one-more-file.pdf', 1024))
            ->usingName('PDF file')
            ->withCustomProperties([
                "title" => "test title",
                "description" => "test description",
            ])
            ->toMediaCollection(config('laravel-medialibrary-graphql.def_media_collection'));

        /** @var Collection $Medias */
        $Medias = $Owner->getMedia(config('laravel-medialibrary-graphql.def_media_collection'));

        // check we have two Media(s)
        $this->assertCount(2, $Medias);

        /** @var Media $Media */
        $Media = $Medias->first();

        // check we get exactly Media
        $this->assertInstanceOf('Spatie\\MediaLibrary\\MediaCollections\\Models\\Media', $Media);

        // execute GraphQL mutation 'deleteMedia'
        $deleteMediaResponse = $this->postGraphQL([
            "query" => 'mutation DeleteMedia($uuid: String!) {
                    deleteMedia(uuid: $uuid)
                }',
            "variables" => [
                "uuid" => $Media->uuid
            ]
        ], [
            'Authorization' => 'Bearer ' . $User->createToken($User->id)->plainTextToken,
        ]);

        // check response
        $deleteMediaResponse
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'deleteMedia',
                ],
            ]);

        $Model = app(config('laravel-medialibrary-graphql.models.main'));
        /** @var HasMedia $FileOwner */
        $FileOwner = $Model->findOrFail($Owner->id);

        /** @var Collection $Medias */
        $MediasAfterDelete = $FileOwner->getMedia(config('laravel-medialibrary-graphql.def_media_collection'));

        // check we have one Media after one was deleted
        $this->assertCount(1, $MediasAfterDelete);
    }
}
