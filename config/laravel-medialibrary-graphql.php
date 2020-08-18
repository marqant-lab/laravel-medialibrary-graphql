<?php

return [

    /**
     * Model(s) to attach media files to
     *
     * by default User model
     *
     * you can specify any model to attach media files
     */
    'models' => [
        'default' => config('auth.providers.users.model'),
        // add other models, example:
        // 'one_more_model' => \Some\Namespace\Model::class,
    ],

    /**
     * API key for web routes requests
     *
     * you can change it any time after publish config
     */
    'apiKey' => env('MEDIA_API_KEY'),

    /**
     * Media collections
     *
     *
     */
    'def_media_collection' => 'downloads',

    /**
     * Flags to manage display or not properties
     *
     * detects if need to send data through GraphQL for specified props
     */
    'properties_flags' => [
        // path to file: 'path' property
        'enable_media_path' => false,
        // file url: 'url' property
        'enable_media_url' => false,
        /**
         * secure download url: 'downloadUrl' property
         * for using these url(s):
         * need to specify MEDIA_API_KEY at the .env and use it at headers
         */
        'enable_media_download_url' => true,
    ],

    /**
     * Validation rules
     *
     *
     */
    'validation_rules' => [
        'upload' => [
            'id'         => 'required|integer',
            'name'       => 'nullable|string',
            'file'       => 'file|mimes:pdf|max:10240', // max size in kilobytes
            'properties' => 'nullable|array',
        ],
        'download' => [
            'uuid' => 'required|uuid',
        ],
        'delete' => [
            'uuid' => 'required|uuid',
        ],
        'delete_all' => [
            'id' => 'required|integer',
        ],
    ],

    /**
     * Pipelines
     *
     * pipelines executed after some events
     *
     * by default it is Pipelines to write logs
     *
     */
    'pipelines' => [
        /**
         * You will get this array as content:
         * [
         *    'action' => 'got files list',
         *    'owner'  => files owner - instance of config('laravel-medialibrary-graphql.models.{model}'),
         *    'model'  => class name of the model from config,
         * ]
         */
        'got_list' => [
            \Marqant\LaravelMediaLibraryGraphQL\Pipelines\GotFilesListAddLogPipeline::class,
        ],
        /**
         * You will get this array as content:
         * [
         *    'action' => 'uploaded file',
         *    'owner'  => file owner - instance of config('laravel-medialibrary-graphql.models.{model}'),
         *    'model'  => class name of the model from config,
         *    'file'   => instance of Illuminate\Http\UploadedFile,
         *    'name'   => param 'name' from GraphQL mutation (file name if it was empty),
         *    'props'  => param 'properties' from GraphQL mutation (empty array if it was empty),
         * ]
         */
        'uploaded' => [
            \Marqant\LaravelMediaLibraryGraphQL\Pipelines\UploadedFileAddLogPipeline::class,
        ],
        /**
         * You will get this array as content:
         * [
         *    'action' => 'downloaded file',
         *    'owner'  => file owner - instance of downloaded file model,
         *    'model'  => class name of the model from config,
         *    'media'  => instance of Spatie\MediaLibrary\MediaCollections\Models\Media,
         * ]
         */
        'downloaded' => [
            \Marqant\LaravelMediaLibraryGraphQL\Pipelines\DownloadedFileAddLogPipeline::class,
        ],
        /**
         * You will get this array as content:
         * [
         *    'action' => 'deleted file',
         *    'owner'  => file owner - instance of deleted file model,
         *    'model'  => class name of the model from config,
         *    'media'  => instance of Spatie\MediaLibrary\MediaCollections\Models\Media,
         * ]
         */
        'deleted' => [
            \Marqant\LaravelMediaLibraryGraphQL\Pipelines\DeletedFileAddLogPipeline::class,
        ],
        /**
         * You will get this array as content:
         * [
         *    'action' => 'deleted all media files',
         *    'owner'  => file owner - instance of config('laravel-medialibrary-graphql.models.{model}'),
         *    'model'  => class name of the model from config,
         * ]
         */
        'deleted_all' => [
            \Marqant\LaravelMediaLibraryGraphQL\Pipelines\DeletedAllMediaFilesAddLogPipeline::class,
        ],
    ],
];
