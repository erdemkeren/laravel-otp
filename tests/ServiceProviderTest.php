<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp {
    if (!function_exists('\Erdemkeren\Otp\config')) {
        function config($key)
        {
            global $testerClass;

            return $testerClass::$functions->config($key);
        }
    }

    if (!function_exists('\Erdemkeren\Otp\config_path')) {
        function config_path($key)
        {
            global $testerClass;

            return $testerClass::$functions->config_path($key);
        }
    }

    if (!function_exists('\Erdemkeren\Otp\database_path')) {
        function database_path($key)
        {
            global $testerClass;

            return $testerClass::$functions->database_path($key);
        }
    }

    if (!function_exists('\Erdemkeren\Otp\resource_path')) {
        function resource_path($key)
        {
            global $testerClass;

            return $testerClass::$functions->resource_path($key);
        }
    }
} // namespace

namespace Erdemkeren\Otp\Test {

    use Closure;
    use Erdemkeren\Otp\Http\Middleware\WebOtp;
    use Erdemkeren\Otp\OtpService;
    use Illuminate\Routing\Router;
    use Illuminate\Contracts\Support\DeferrableProvider;
    use Mockery as M;
    use Illuminate\Contracts\Foundation\Application;
    use Erdemkeren\Otp\ServiceProvider;
    use PHPUnit\Framework\MockObject\MockObject;
    use PHPUnit\Framework\TestCase;

    class ServiceProviderTest extends TestCase
    {
        public static $functions = [];

        private ServiceProvider $provider;

        private MockObject|Application $app;

        private MockObject|Router $router;

        protected function setUp(): void
        {
            parent::setUp();

            self::$functions = M::mock();
            global $testerClass;
            $testerClass = self::class;

            $this->router = $this->createMock(Router::class);
            $this->app = $this->createMock(Application::class);

            $this->provider = new ServiceProvider($this->app);
        }

        /**
         * @test
         */
        public function itPublishesTheConfigurableFiles(): void
        {
            $this::$functions
                ->shouldReceive('config_path')
                ->once()
                ->with('otp.php')
                ->andReturn(':otp.php:');

            $this::$functions
                ->shouldReceive('database_path')
                ->once()
                ->with('migrations')
                ->andReturn(':migrations:');

            $this::$functions
                ->shouldReceive('resource_path')
                ->once()
                ->with('views')
                ->andReturn(':views:');

            $this->provider->boot();
            $dir = fn(string $path): string => sprintf(
                '%s/%s',
                dirname(__FILE__, 2),
                $path,
            );

            $this->assertEquals([
                $dir('src/../config/otp.php') => ':otp.php:',
                $dir('src/../database/migrations/') => ':migrations:',
                $dir('src/../views/') => ':views:',
            ], $this->provider->pathsToPublish('Erdemkeren\Otp\ServiceProvider'));
        }

        /**
         * @test
         */
        public function itRegistersASingletonOtpServiceInstance(): void
        {
            $this::$functions
                ->shouldReceive('config')
                ->once()
                ->with('otp.default_format')
                ->andReturn(':otp.default_format:');

            $this::$functions
                ->shouldReceive('config')
                ->once()
                ->with('app.key')
                ->andReturn(':app.key:');

            $this->app
                ->expects($this->once())
                ->method('singleton')
                ->with(
                    'otp',
                    $this->callback(
                        fn(Closure $callback): bool =>
                            is_callable($callback)
                            && $callback() instanceof OtpService
                    )
                );

            $this->app
                ->expects($this->once())
                ->method('get')
                ->with('router')
                ->willReturn($this->router);

            $this->router
                ->expects($this->once())
                ->method('aliasMiddleware')
                ->with('otp', WebOtp::class);

            $this->provider->register();
        }

        /**
         * @test
         */
        public function itIsDeferrable(): void
        {
            $this->assertInstanceOf(DeferrableProvider::class, $this->provider);
        }

        /**
         * @test
         */
        public function itReportsTheProvidedService()
        {
            $this->assertEquals('otp', $this->provider->provides()[0]);
        }
    }
} // namespace
