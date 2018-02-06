<?php

namespace Erdemkeren\TemporaryAccess;

use UnexpectedValueException;
use Illuminate\Support\ServiceProvider;
use Erdemkeren\TemporaryAccess\Token\TokenGenerator;
use Erdemkeren\TemporaryAccess\Contracts\AccessTokenRepositoryInterface;
use Erdemkeren\TemporaryAccess\Token\TokenGenerator\TokenGeneratorInterface;

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
        $this->app->singleton(AccessTokenRepositoryInterface::class, function () {
            return new DatabaseAccessTokenRepository($this->app['db']->connection(),
                config('temporary_access.table', 'temporary_access_tokens'), config('temporary_access.expires', 15));
        });

        $this->app->singleton(TokenGeneratorInterface::class, function () {
            $generator = config('temporary_access.token_generator', 'string');
            $generators = $this->getTokenGenerators();
            if (! array_key_exists($generator, $generators)) {
                throw new UnexpectedValueException(
                    "The access token generator [$generator] could not be found."
                );
            }

            return $generators[$generator](config('app.secret'));
        });
    }

    public function provides()
    {
        return [
            TokenGeneratorInterface::class,
            AccessTokenRepositoryInterface::class,
        ];
    }

    private function getTokenGenerators()
    {
        return [
            'string' => function ($key) {
                return new TokenGenerator\StringTokenGenerator($key);
            },
            'numeric' => function ($key) {
                return new TokenGenerator\NumericTokenGenerator($key);
            },
            'numeric-no-0' => function ($key) {
                return new TokenGenerator\NumericNo0TokenGenerator($key);
            },
        ];
    }
}
