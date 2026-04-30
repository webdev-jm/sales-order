<?php

namespace App\Providers;

use App\Services\HubUserResolver;
use BeviWebdevJm\HubAuthClient\Contracts\ResolvesHubUser;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(ResolvesHubUser::class, HubUserResolver::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('yyyy_a_number', 'App\Providers\CustomValidationRules@validateYyyyANumber');
        Paginator::useBootstrap();

        $mainPath = database_path('migrations');
        $directories = File::directories($mainPath); // Get all subdirectories
        
        $paths = array_merge([$mainPath], $directories); // Include the main migrations folder and its immediate subdirectories

        // If you have nested subdirectories and want to include them, you can extend this logic.
        // For example, to recursively find all directories:
        // $allPaths = collect($paths)->flatMap(function ($path) {
        //     return File::directories($path);
        // })->toArray();
        // $paths = array_merge($paths, $allPaths);


        $this->loadMigrationsFrom($paths);
    }
}
