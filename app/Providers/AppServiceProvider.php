<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * AppServiceProvider
 *
 * App-wide service provider.
 *
 * Use cases
 * - bind interfaces to implementations
 * - register singletons
 * - register app-level boot logic
 *
 * Your current system does not rely on custom bindings here yet.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register services in the container.
     */
    public function register(): void
    {
        //
    }

    /**
     * Boot services after all providers are registered.
     */
    public function boot(): void
    {
        //
    }
}