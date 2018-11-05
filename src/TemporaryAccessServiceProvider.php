<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\TemporaryAccess;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Erdemkeren\TemporaryAccess\Http\Middleware\OtpAccess;
use Erdemkeren\TemporaryAccess\PasswordGenerators as Generators;

class TemporaryAccessServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    public function boot(): void
    {
        $this->publishes([$this->configPath() => config_path('temporary_access.php')]);
        $this->publishes([$this->migrationPath() => database_path('migrations')]);
        $this->publishes([$this->viewPath() => resource_path('views')]);
    }

    /**
     * Register the temporary access service.
     */
    public function register(): void
    {
        $service = $this->createServiceInstance();
        $this->registerDefaultPasswordGenerators($service);

        $this->app->singleton('temporary-access', function () use ($service) {
            return $service;
        });

        /** @var Router $router */
        $router = $this->app['router'];
        $router->aliasMiddleware('otp-access', OtpAccess::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return [
            'temporary-access',
        ];
    }

    /**
     * Create a new temporary access service instance.
     *
     * @return TemporaryAccessService
     */
    private function createServiceInstance(): TemporaryAccessService
    {
        return new TemporaryAccessService(
            new PasswordGeneratorManager(),
            new Encryptor(config('app.secret')),
            config('temporary_access.password_generator', 'string'),
            6
        );
    }

    /**
     * Register default password generators to the
     * given temporary access service instance.
     *
     * @param TemporaryAccessService $service
     */
    private function registerDefaultPasswordGenerators($service): void
    {
        $service->addPasswordGenerator('string', Generators\StringPasswordGenerator::class);
        $service->addPasswordGenerator('numeric', Generators\NumericPasswordGenerator::class);
        $service->addPasswordGenerator('numeric-no-0', Generators\NumericNo0PasswordGenerator::class);
    }

    /**
     * Get the project config path.
     *
     * @return string
     */
    private function configPath()
    {
        return __DIR__.'/../config/temporary_access.php';
    }

    /**
     * Get the migration path.
     *
     * @return string
     */
    private function migrationPath()
    {
        return __DIR__.'/../database/migrations/';
    }

    /**
     * Get the view path.
     *
     * @return string
     */
    private function viewPath()
    {
        return __DIR__.'/../views/';
    }
}
