<?php

namespace Marqant\LaravelMediaLibraryGraphQL\Contracts;

use Closure;

/** @package Marqant\LaravelMediaLibraryGraphQL\Contracts */
interface Pipe
{
    public function handle($content, Closure $next);
}
