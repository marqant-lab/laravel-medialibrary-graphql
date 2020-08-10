<?php

namespace Marqant\LaravelMediaLibraryGraphQL\Tests\GraphQL\Mutations;

use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Class DownloadMediaTest
 *
 * @package Marqant\LaravelMediaLibraryGraphQL\Tests\GraphQL\Mutations
 */
class DownloadMediaTest extends TestCase
{
    use MakesGraphQLRequests;

    /**
     * @group GraphQLMediaLibrary
     *
     * @test
     */
    public function testDownloadOwnerMediaFile()
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

        /** @var Collection $Medias */
        $Medias = $Owner->getMedia(config('laravel-medialibrary-graphql.def_media_collection'));

        // check we have one Media
        $this->assertCount(1, $Medias);
        /** @var Media $Media */
        $Media = $Medias->first();
        // check we get exactly Media
        $this->assertInstanceOf('Spatie\\MediaLibrary\\MediaCollections\\Models\\Media', $Media);

        // execute GraphQL mutation 'downloadMedia'
        $downloadMediaResponse = $this->postGraphQL([
            "query" => 'mutation DownloadMedia($uuid: String!) {
                    downloadMedia(uuid: $uuid)
                }',
            "variables" => [
                "uuid" => $Media->uuid
            ]
        ], [
            'Authorization' => 'Bearer ' . $User->createToken($User->id)->plainTextToken,
        ]);

        // check response
        $downloadMediaResponse
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'downloadMedia',
                ],
            ]);

        $fileBase64 = $downloadMediaResponse->json('data.downloadMedia');
        $checkBase64 = $this->is_base64($fileBase64);

        // check if received valid Base64 file string
        $this->assertTrue($checkBase64);
    }

    /**
     * @param string $str
     *
     * @return bool
     */
    private function is_base64(string $str): bool
    {
        if ( base64_encode(base64_decode($str, true)) === $str) return true;
        else return false;
    }
}
