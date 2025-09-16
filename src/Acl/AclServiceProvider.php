<?php

namespace Orangesix\Acl;

use Illuminate\Support\ServiceProvider;
use Orangesix\Acl\Http\Middleware\AclMiddleware;

class AclServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        /**
         * Register facade
         */
        $this->app->singleton('acl', function ($app) {
            return new Acl();
        });

        /**
         * Register Middleware
         */
        $this->app['router']->aliasMiddleware('acl', AclMiddleware::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/Config/acl.php' => config_path('acl.php')], 'acl-config');
            $this->publishes([__DIR__ . '/Database/seeders/' => database_path('seeders')], 'acl-seeders');
            $this->publishes([__DIR__ . '/Database/migrations/' => database_path('migrations')], 'acl-migrations');
        }
    }
}
