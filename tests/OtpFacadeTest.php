<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Erdemkeren\Otp\OtpFacade
 */
class OtpFacadeTest extends TestCase
{
    public function testItProvidesOtpFacadeAccessorName(): void
    {
        $app = new Container();

        $app->singleton('app', 'Illuminate\Container\Container');
        $app->singleton('config', 'Illuminate\Config\Repository');
        $app->singleton('otp', function () {
            return new class() {
                public function create($a, $b): string
                {
                    return $a.$b;
                }
            };
        });

        Facade::setFacadeApplication($app);

        $result = OtpFacade::create('foo', 6);
        $this->assertSame('foo6', $result);

        Facade::clearResolvedInstances();
    }
}
