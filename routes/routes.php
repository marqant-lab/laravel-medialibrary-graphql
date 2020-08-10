<?php


Route::get('media/download/{uuid}', function ($uuid) {
    try {
        /** @var Spatie\MediaLibrary\MediaCollections\Models\Media $Media */
        $Media = Spatie\MediaLibrary\MediaCollections\Models\Media::query()
            ->where('uuid', $uuid)
            ->firstOrFail();
    } catch (Exception $exception) {
        throw new Exception(__("Can't find Media file by UUID: '$uuid'! Use url(s) to download files only from list."));
    }

    return $Media;
})->middleware(\Marqant\LaravelMediaLibraryGraphQL\Middleware\HasApiKey::class);
