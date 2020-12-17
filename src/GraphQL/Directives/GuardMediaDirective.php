<?php

namespace Marqant\LaravelMediaLibraryGraphQL\GraphQL\Directives;

use Nuwave\Lighthouse\Schema\Directives\GuardDirective;

/**
 * Class GuardMediaDirective
 *
 * @package Marqant\LaravelMediaLibraryGraphQL\GraphQL\Directives
 */
class GuardMediaDirective extends GuardDirective
{
    public static function definition(): string
    {
        return /** @lang GraphQL */ <<<'SDL'
"""
Run authentication through one or more guards.
This is run per field and may allow unauthenticated
users to still receive partial results.
"""
directive @guardMedia(
  """
  Specify which guards to use, e.g. ["api"].
  When not defined, the default from `laravel-medialibrary-graphql.php` is used.
  """
  with: [String!]
) on FIELD_DEFINITION | OBJECT
SDL;
    }

    /**
     * Determine if the user is logged in to any of the given guards.
     *
     * @param  string[]  $guards
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function authenticate(array $guards): void
    {
        if (empty($guards)) {
            $guard = config("laravel-medialibrary-graphql.guard");
            if (empty($guard)) {
                $guards = [config('lighthouse.guard')];
            } elseif (is_array($guard)) {
                $guards = $guard;
            } elseif (is_string($guard)) {
                $guards = [$guard];
            }
        }

        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                $this->auth->shouldUse($guard);

                return;
            }
        }

        $this->unauthenticated($guards);
    }
}
