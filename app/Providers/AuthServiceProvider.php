<?php

namespace App\Providers;

use App\Models\FileItem;
use App\Policies\FileItemPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

/**
 * AuthServiceProvider
 *
 * Registers model-to-policy mappings.
 *
 * Why it matters
 * - Enables $this->authorize('update', $fileItem) in controllers.
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Policy mappings.
     */
    protected $policies = [
        FileItem::class => FileItemPolicy::class,
    ];

    /**
     * Boot auth services.
     */
    public function boot(): void
    {
        //
    }
}