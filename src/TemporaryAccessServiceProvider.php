<?php

namespace Erdemkeren\TemporaryAccess;

use Illuminate\Support\ServiceProvider;
use Erdemkeren\TemporaryAccess\Contracts\AccessTokenRepository;
use Erdemkeren\TemporaryAccess\Contracts\AccessCodeGenerator as AccessCodeGeneratorContract;

class TemporaryAccessServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'migrations');

        $this->publishes([
            __DIR__.'/../config/temporary_access.php' => config_path('temporary_access.php'),
        ]);
    }

    public function register()
    {
        $this->app->singleton(AccessTokenRepository::class, function ($app) {
            return new DatabaseAccessTokenRepository($this->app['db']->connection(),
                config('temporary_access.table', 'temporary_access_tokens'), config('temporary_access.expires', 15));
        });

        $this->app->singleton(AccessCodeGeneratorContract::class, function ($app) {
            return new AccessCodeGenerator(config('app.key'));
        });
    }

    public function provides()
    {
        return [
            AccessTokenRepository::class,
            AccessCodeGeneratorContract::class,
        ];
    }
}
