<?php

namespace Marqant\LaravelMediaLibraryGraphQL\Pipelines;

use Closure;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Marqant\LaravelMediaLibraryGraphQL\Contracts\Pipeline;

/**
 * Class DeletedFileAddLogPipeline
 *
 * @package Marqant\LaravelMediaLibraryGraphQL\Pipelines
 */
class DeletedFileAddLogPipeline implements Pipeline
{
    /**
     * @param         $content
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle($content, Closure $next)
    {
        /** @var HasMedia|Model $FileOwner */
        $FileOwner = $content['owner'];
        /** @var Media $Media */
        $Media = $content['media'];

        Log::info("\n    User: " . Auth::user()->name . "\n    {$content['action']} '"
            . $Media->name . "' (" . $Media->file_name . ")\n    of the model '"
            . config('laravel-medialibrary-graphql.models.main') . "' (ID: {$FileOwner->id})\n");

        return  $next($content);
    }
}
