<?php

namespace Marqant\LaravelMediaLibraryGraphQL\Pipelines;

use Closure;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Marqant\LaravelMediaLibraryGraphQL\Contracts\Pipe;

class DeletedAllMediaFilesAddLogPipeline implements Pipe
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

        Log::info("\n    User: " . Auth::user()->name . "\n    {$content['action']} '\n    of the model '"
            . config('laravel-medialibrary-graphql.models.main') . "' (ID: {$FileOwner->id})\n");

        return  $next($content);
    }
}
