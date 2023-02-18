<?php

namespace App\Providers;

use App\Models\Device;
use App\Models\Group;
use App\Observers\DeviceObserver;
use App\Observers\GroupObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        Blade::if('routeis', function ($expression) {
            return fnmatch($expression, Route::currentRouteName());
        });
        Group::observe(GroupObserver::class);
        Device::observe(DeviceObserver::class);
        Paginator::useBootstrap();
    }
}
