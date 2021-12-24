<?php

/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp;

use Illuminate\Routing\Router;
use Erdemkeren\Otp\Formats\Acme\Acme;
use Erdemkeren\Otp\Http\Middleware\WebOtp;
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
        $this->registerFormats($service);

        $this->app->singleton('otp', function () use ($service) {
            return $service;
        });

        /** @var Router $router */
        $router = $this->app['router'];
        $router->aliasMiddleware('otp', WebOtp::class);
    }

    public function provides(): array
    {
        return [
            'otp',
        ];
    }

    protected function registerFormats(OtpService $service): void
    {
        $service->addFormat(new Acme());
    }

    private function createServiceInstance(): OtpService
    {
        return new OtpService(
            new FormatManager(config('otp.default_format')),
            new Encryptor(config('app.key')),
            new DatabaseTokenRepository(),
        );
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
