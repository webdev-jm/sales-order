<?php

namespace App\Providers;

use App\Support\FormBuilder;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\ServiceProvider;

class FormServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('form', function ($app) {
            return new FormBuilder(
                $app->make(UrlGenerator::class),
                $app->make(Session::class),
            );
        });
    }
}
