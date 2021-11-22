<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp\Test;

use Erdemkeren\Otp\Exceptions\UnregisteredGeneratorException;
use Erdemkeren\Otp\GeneratorManager;
use PHPUnit\Framework\TestCase;

class GeneratorManagerTest extends TestCase
{
    private GeneratorManager $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = tap(
            new GeneratorManager(),
            fn (GeneratorManager $m) => $m->register('default', fn (): string => ':default:'),
        );
    }

    /**
     * @test
     */
    public function itReturnsTheRequestedManager(): void
    {
        $generator = $this->manager->get('default');

        $this->assertEquals(':default:', $generator());
    }

    /**
     * @test
     */
    public function itThrowsExceptionIfTheRequestedManagerIsNotFound(): void
    {
        $this->expectException(UnregisteredGeneratorException::class);

        $this->manager->get(':undefined-token-generator:');
    }
}
