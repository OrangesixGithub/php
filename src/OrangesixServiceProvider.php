<?php

namespace Orangesix;

use Illuminate\Support\ServiceProvider;

class OrangesixServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__) . '/config/orangesix.php', 'orangesix');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                dirname(__DIR__) . '/config/orangesix.php' => config_path('orangesix.php'),
            ], 'orangesix-config');
        }
    }
}
