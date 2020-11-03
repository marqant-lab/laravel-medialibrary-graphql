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
 * Class CreatedNewMediaAddLogPipeline
 *
 * @package Marqant\LaravelMediaLibraryGraphQL\Pipelines
 */
class CreatedNewMediaAddLogPipeline implements Pipe
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

        Log::info("\n    User: " . Auth::user()->name . "\n    successfully {$content['action']} '{$Media->name}' "
            . $Media->file_name . "\n    to the model '" . $content['model']
            . "' (ID: {$FileOwner->id})\n");

        return  $next($content);
    }
}
