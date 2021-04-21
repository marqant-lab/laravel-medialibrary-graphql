<?php

namespace Marqant\LaravelMediaLibraryGraphQL\Services;

use Exception;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Marqant\LaravelMediaLibraryGraphQL\Resources\MediaResource;

/**
 * Class MediaLibraryService
 *
 * @package Marqant\LaravelMediaLibraryGraphQL\Services
 */
class MediaLibraryService
{
    /**
     * Get Media(s) for given owner
     *
     * @param array $args
     *
     * @return array
     * @throws Exception
     */
    public function getMedia(array $args): array
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

    /**
     * Get Media in base64 format
     *
     * @param array $args
     *
     * @return string
     * @throws Exception
     */
    public function downloadMedia(array $args): string
    {
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
            'model'  => get_class($FileOwner),
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

    /**
     * Upload received file
     *
     * @param array $args
     *
     * @return array
     * @throws Exception
     */
    public function uploadFile(array $args): array
    {
        $model_key   = strtolower($args['model'] ?? 'default');
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
            Log::error('Marqant\LaravelMediaLibraryGraphQL\GraphQL\Mutations\UploadFile ERROR ' .
                "\n  params: " . print_r($args, true) .
                "\n  error: " . $exception->getMessage() .
                "\n  at: " . $exception->getFile() .
                "\n line: " . $exception->getLine());

            throw new Exception(__("Something went wrong! Please contact support."));
        }
    }

    /**
     * Delete Media by 'uuid'
     *
     * @param array $args
     *
     * @return void
     * @throws Exception
     */
    public function deleteMedia(array $args)
    {
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
            'action' => 'deleted file',
            'media'  => $Media,
            'owner'  => $FileOwner,
            'model'  => get_class($FileOwner),
        ];

        // execute pipelines and delete file after
        app(Pipeline::class)
            ->send($pipelines_data)
            ->through(config('laravel-medialibrary-graphql.pipelines.deleted'))
            ->then(
                function () use ($Media) {
                    $Media->delete();
                }
            );

        return;
    }

    /**
     * Delete all Media(s) for given owner
     *
     * @param array $args
     *
     * @return void
     * @throws Exception
     */
    public function deleteAllMedia(array $args)
    {
        $model_key   = $args['model'] ?? 'default';
        $model_class = config("laravel-medialibrary-graphql.models.$model_key");
        $Model       = app($model_class);

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
            'model'  => $model_class,
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

    /**
     * @param array $args
     *
     * @throws Exception
     */
    public function validateUploadFile(array $args)
    {
        $validator = Validator::make($args, config('laravel-medialibrary-graphql.validation_rules.upload'));

        if ($validator->fails()) {
            $namespace = 'Marqant\LaravelMediaLibraryGraphQL\GraphQL\Mutations\UploadFile';
            Log::error("$namespace validation errors: \n" .
                print_r($validator->errors(), true) .
                "\n  params: " . print_r($args, true));

            $message = "Trying to upload not valid file. Please try another one or contact support to check logs.";
            throw new Exception(__($message));
        }
    }

    /**
     * @param array $args
     *
     * @throws Exception
     */
    public function validate(array $args, string $key)
    {
        $validator = Validator::make($args, config('laravel-medialibrary-graphql.validation_rules.' . $key));

        if ($validator->fails()) {
            Log::error("Delete Media validation errors: \n" .
                print_r($validator->errors(), true) .
                "\n  params: " . print_r($args, true));

            throw new Exception(__("Empty or wrong param(s)."));
        }
    }
}
