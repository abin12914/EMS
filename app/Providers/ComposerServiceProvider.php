<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class ComposerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //current user to all views
        View::composer('*', "App\Http\ViewComposers\AllViewComposer");
        //accounts to views
        View::composer('components.selects.accounts', "App\Http\ViewComposers\AccountComponentComposer");
        //employees to views
        View::composer('components.selects.employees', "App\Http\ViewComposers\EmployeeComponentComposer");
        //excavators to views
        View::composer('components.selects.excavators', "App\Http\ViewComposers\ExcavatorComponentComposer");
        //sites to views
        View::composer('components.selects.sites', "App\Http\ViewComposers\SiteComponentComposer");
        //services to views
        View::composer('components.selects.services', "App\Http\ViewComposers\ServiceComponentComposer");
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
