<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\TemporaryAccess;

use PHPUnit\Framework\TestCase;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;

/**
 * @covers \Erdemkeren\TemporaryAccess\TemporaryAccessFacade
 */
class TemporaryAccessFacadeTest extends TestCase
{
    public function testItProvidesTemporaryAccessFacadeAccessorName(): void
    {
        $app = new Container();

        $app->singleton('app', 'Illuminate\Container\Container');
        $app->singleton('config', 'Illuminate\Config\Repository');
        $app->singleton('temporary-access', function () {
            return new class() {
                public function create($a, $b): string
                {
                    return $a.$b;
                }
            };
        });

        Facade::setFacadeApplication($app);

        $result = TemporaryAccessFacade::create('foo', 6);
        $this->assertSame('foo6', $result);

        Facade::clearResolvedInstances();
    }
}
