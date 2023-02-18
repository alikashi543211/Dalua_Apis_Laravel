<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class ValidatorServiceProvider extends ServiceProvider
{
    public function boot()
    {

        // Check exists where not equal to value
        Validator::extend('not_auth', function ($attribute, $value, $parameters) {
            return DB::table($parameters[0])->where($parameters[1], '!=', Auth::id())->where($parameters[1], $value)->exists();
        }, 'The selected :attribute is invalid.');
    }

    public function register()
    {
        //
    }
}
