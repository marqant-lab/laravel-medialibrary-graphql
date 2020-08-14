<?php

namespace Marqant\LaravelMediaLibraryGraphQL\Tests;

use Tests\CreatesApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;
    use MakesGraphQLRequests;
}
