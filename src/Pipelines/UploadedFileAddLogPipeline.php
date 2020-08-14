<?php

namespace Marqant\LaravelMediaLibraryGraphQL\Pipelines;

use Closure;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Marqant\LaravelMediaLibraryGraphQL\Contracts\Pipe;

/**
 * Class UploadedFileAddLogPipeline
 *
 * @package Marqant\LaravelMediaLibraryGraphQL\Pipelines
 */
class UploadedFileAddLogPipeline implements Pipe
{
    /**
     * @param         $content
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle($content, Closure $next)
    {
        /** @var UploadedFile $File */
        $File = $content['file'];
        /** @var HasMedia|Model $FileOwner */
        $FileOwner = $content['owner'];

        Log::info("\n    User: " . Auth::user()->name . "\n    {$content['action']} '{$content['name']}' "
            . $File->getClientOriginalName() . "\n    to the model '"
            . config('laravel-medialibrary-graphql.models.main')
            . "' (ID: {$FileOwner->id})\n    with properties: " . print_r($content['props'], true));

        return  $next($content);
    }
}
