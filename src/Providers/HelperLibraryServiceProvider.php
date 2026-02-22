<?php

namespace Keysoft\HelperLibrary\Providers;

use Illuminate\Support\ServiceProvider;

class HelperLibraryServiceProvider extends ServiceProvider
{
    public function register()
    {
        // bind repository kalau perlu
    }

    public function boot()
    {
        // load migrations kalau ada
        // $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}