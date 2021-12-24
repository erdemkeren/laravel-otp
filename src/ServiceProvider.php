<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp;

use Illuminate\Routing\Router;
use Erdemkeren\Otp\Http\Middleware\Otp;
use Illuminate\Contracts\Support\DeferrableProvider;
use Erdemkeren\Otp\Repositories\DatabaseTokenRepository;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider implements DeferrableProvider
{
    public function boot(): void
    {
        $this->publishes([$this->configPath() => config_path('otp.php')]);
        $this->publishes([$this->migrationPath() => database_path('migrations')]);
        $this->publishes([$this->viewPath() => resource_path('views')]);
    }

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

    public function provides(): array
    {
        return [
            'otp',
        ];
    }

    private function createServiceInstance(): OtpService
    {
        return new OtpService(
            new FormatManager(config('opt.default.format')),
            new Encryptor(config('app.secret')),
            new DatabaseTokenRepository(),
        );
    }

    /**
     * Register default password generators to the
     * given otp service instance.
     *
     * @param  OtpService  $service
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
