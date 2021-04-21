<?php

namespace Marqant\LaravelMediaLibraryGraphQL\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class MediaLibrary
 *
 * @package Marqant\LaravelMediaLibraryGraphQL\Facades
 *
 * @mixin \Marqant\LaravelMediaLibraryGraphQL\Services\MediaLibraryService
 */
class MediaLibrary extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-medialibrary';
    }
}
