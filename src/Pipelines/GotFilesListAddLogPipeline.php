<?php

namespace Marqant\LaravelMediaLibraryGraphQL\Pipelines;

use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Marqant\LaravelMediaLibraryGraphQL\Contracts\Pipeline;

/**
 * Class GotFilesListAddLogPipeline
 *
 * @package Marqant\LaravelMediaLibraryGraphQL\Pipelines
 */
class GotFilesListAddLogPipeline implements Pipeline
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
            . config('laravel-medialibrary-graphql.models.main')
            . "' (ID: {$content['owner']->id})\n");

        return  $next($content);
    }
}
