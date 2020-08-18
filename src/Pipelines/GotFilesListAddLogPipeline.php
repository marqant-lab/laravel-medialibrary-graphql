<?php

namespace Marqant\LaravelMediaLibraryGraphQL\Pipelines;

use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Marqant\LaravelMediaLibraryGraphQL\Contracts\Pipe;

/**
 * Class GotFilesListAddLogPipeline
 *
 * @package Marqant\LaravelMediaLibraryGraphQL\Pipelines
 */
class GotFilesListAddLogPipeline implements Pipe
{
    /**
     * @param         $content
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle($content, Closure $next)
    {
        Log::info("\n    User: " . Auth::user()->name . "\n    {$content['action']}\n    of the model '"
            . $content['model'] . "' (ID: {$content['owner']->id})\n");

        return  $next($content);
    }
}
