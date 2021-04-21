<?php

namespace Marqant\LaravelMediaLibraryGraphQL\Providers;

use Illuminate\Support\ServiceProvider;
use Marqant\LaravelMediaLibraryGraphQL\Services\MediaLibraryService;

/**
 * Class LaravelMediaLibraryGraphQLServiceProvider
 *
 * @package Marqant\LaravelMediaLibraryGraphQL\Providers
 */
class LaravelMediaLibraryGraphQLServiceProvider extends ServiceProvider
{
    public function register()
    {
        //////////////////////////////////
        // Config //
        //////////////////////////////////
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/laravel-medialibrary-graphql.php',
            'laravel-medialibrary-graphql'
        );
        //////////////////////////////////
        // Custom Queries //
        //////////////////////////////////

        $this->registerQueries();

        //////////////////////////////////
        // Custom Mutations //
        //////////////////////////////////

        $this->registerMutations();

        //////////////////////////////////
        // Custom Directives //
        //////////////////////////////////

        $this->registerDirectives();

        //////////////////////////////////
        // Services //
        //////////////////////////////////
        $this->bindServices();
    }

    public function boot()
    {
        // Migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Make Config publishable
        $this->publishes([
            __DIR__ . '/../../config/laravel-medialibrary-graphql.php' =>
                config_path('laravel-medialibrary-graphql.php'),
        ], 'config');

        // routes
        $this->mapApiRoutes();
    }

    public function registerQueries()
    {
        config([
            'lighthouse.namespaces.queries' => array_merge(
                (array) config('lighthouse.namespaces.queries'),
                (array) 'Marqant\\LaravelMediaLibraryGraphQL\\GraphQL\\Queries'
            ),
        ]);
    }

    public function registerMutations()
    {
        config([
            'lighthouse.namespaces.mutations' => array_merge(
                (array) config('lighthouse.namespaces.mutations'),
                (array) 'Marqant\\LaravelMediaLibraryGraphQL\\GraphQL\\Mutations'
            ),
        ]);
    }

    public function registerDirectives()
    {
        config([
            'lighthouse.namespaces.directives' => array_merge(
                (array) config('lighthouse.namespaces.directives'),
                (array) 'Marqant\\LaravelMediaLibraryGraphQL\\GraphQL\\Directives'
            ),
        ]);
    }

    private function mapApiRoutes()
    {
//        Route::prefix('api')
//            ->group(dirname(__FILE__) . '/../../routes/routes.php');
        $this->loadRoutesFrom(__DIR__ . '/../../routes/routes.php');
    }

    /**
     * Method to setup service bindings and stuff to be used in facades of this package.
     *
     * @return void
     */
    private function bindServices()
    {
        // MediaLibraryService as MediaLibrary
        $this->app->singleton('laravel-medialibrary', function ($app) {
            return new MediaLibraryService();
        });
    }
}
