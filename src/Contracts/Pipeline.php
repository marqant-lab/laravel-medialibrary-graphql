<?php

namespace Marqant\LaravelMediaLibraryGraphQL\Contracts;

use Closure;

/**
 * Interface Pipeline
 *
 * @package Marqant\LaravelMediaLibraryGraphQL\Contracts
 */
interface Pipeline
{
    public function handle($content, Closure $next);
}
