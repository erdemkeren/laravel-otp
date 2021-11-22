<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp;

use Erdemkeren\Otp\Generators;
use Erdemkeren\Otp\Repositories\DatabaseTokenRepository;
use Illuminate\Routing\Router;
use Erdemkeren\Otp\Http\Middleware\Otp;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

/**
 * Class ServiceProvider.
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected bool $defer = true;

    public function boot(): void
    {
        $this->publishes([$this->configPath() => config_path('otp.php')]);
        $this->publishes([$this->migrationPath() => database_path('migrations')]);
        $this->publishes([$this->viewPath() => resource_path('views')]);
    }

    /**
     * Register the otp service.
     *
     * @return void
     */
    public function register(): void
    {
        $service = $this->createServiceInstance();
        $this->registerDefaultPasswordGenerators($service);

        $this->app->singleton('otp', function () use ($service) {
            return $service;
        });

        /** @var Router $router */
        $router = $this->app['router'];
        $router->aliasMiddleware('otp', Otp::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return [
            'otp',
        ];
    }

    /**
     * Create a new otp service instance.
     *
     * @return OtpService
     */
    private function createServiceInstance(): OtpService
    {
        return new OtpService(
            new GeneratorManager(),
            new Encryptor(config('app.secret')),
            new DatabaseTokenRepository()
        );
    }

    /**
     * Register default password generators to the
     * given otp service instance.
     *
     * @param OtpService $service
     */
    private function registerDefaultPasswordGenerators($service): void
    {
        $service->addPasswordGenerator('string', Generators\StringGenerator::class);
        $service->addPasswordGenerator('numeric', Generators\NumericGenerator::class);
        $service->addPasswordGenerator('numeric-no-0', Generators\NumericNo0Generator::class);
    }

    /**
     * Get the project config path.
     *
     * @return string
     */
    private function configPath(): string
    {
        return __DIR__.'/../config/otp.php';
    }

    /**
     * Get the migration path.
     *
     * @return string
     */
    private function migrationPath(): string
    {
        return __DIR__.'/../database/migrations/';
    }

    /**
     * Get the view path.
     *
     * @return string
     */
    private function viewPath(): string
    {
        return __DIR__.'/../views/';
    }
}
