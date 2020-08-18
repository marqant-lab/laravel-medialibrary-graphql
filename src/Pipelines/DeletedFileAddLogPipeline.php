<?php

namespace Marqant\LaravelMediaLibraryGraphQL\Pipelines;

use Closure;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Marqant\LaravelMediaLibraryGraphQL\Contracts\Pipe;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Class DeletedFileAddLogPipeline
 *
 * @package Marqant\LaravelMediaLibraryGraphQL\Pipelines
 */
class DeletedFileAddLogPipeline implements Pipe
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
            . $content['model'] . "' (ID: {$FileOwner->id})\n");

        return  $next($content);
    }
}
