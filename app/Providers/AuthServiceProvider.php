<?php

namespace App\Providers;

use App\Models\FileItem;
use App\Policies\FileItemPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        FileItem::class => FileItemPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
