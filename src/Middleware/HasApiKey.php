<?php

namespace Marqant\LaravelMediaLibraryGraphQL\Middleware;

use Closure;
use Illuminate\Http\Response;

/**
 * Class HasApiKey
 *
 * @package Marqant\LaravelMediaLibraryGraphQL\Middleware
 */
class HasApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (empty($request->header('apiKey'))
            || $request->header('apiKey') !== config('laravel-medialibrary-graphql.apiKey')) {
            return response()->json('Invalid request! Empty or invalid api key', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $next($request);
    }
}
