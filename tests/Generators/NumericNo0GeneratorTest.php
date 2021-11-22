<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp\Generators;

use function function_exists;

if (! function_exists('\Erdemkeren\Otp\Generators\random_int')) {
    function random_int($min, $max)
    {
        global $testerClass;

        return $testerClass::$functions->random_int($min, $max);
    }
}

if (! function_exists('\Erdemkeren\Otp\Generators\rand')) {
    function rand($min, $max)
    {
        global $testerClass;

        return $testerClass::$functions->rand($min, $max);
    }
}

namespace Erdemkeren\Otp\Test\Generators;

use Exception;
use Mockery as M;
use PHPUnit\Framework\TestCase;
use Erdemkeren\Otp\Generators\NumericNo0Generator;

/**
 * @covers \Erdemkeren\Otp\Generators\NumericNo0Generator
 */
class NumericNo0GeneratorTest extends TestCase
{
    public static M\MockInterface $functions;

    private NumericNo0Generator $passwordGenerator;

    public function setUp(): void
    {
        self::$functions = M::mock();

        global $testerClass;
        $testerClass = self::class;

        $this->passwordGenerator = new NumericNo0Generator();
    }

    public function tearDown(): void
    {
        M::close();
    }

    /**
     * @test
     */
    public function itGeneratesRandomNumericTokensWithoutZeroes(): void
    {
        $this::$functions->shouldReceive('random_int')
            ->once()
            ->with(10000000, 99999999)
            ->andReturn(103410);

        $this::$functions->shouldReceive('random_int')
            ->once()
            ->with(1, 9)
            ->andReturn(6);

        $this->assertSame('163416', $this->passwordGenerator->generate());
    }

    /**
     * @test
     */
    public function itUsesRandWhenRandomIntDontWork(): void
    {
        $this::$functions->shouldReceive('random_int')
            ->twice()
            ->andThrow(Exception::class);

        $this::$functions->shouldReceive('rand')
            ->once()
            ->with(10000000, 99999999)
            ->andReturn(1034510);

        $this::$functions->shouldReceive('rand')
            ->once()
            ->with(1, 9)
            ->andReturn(7);

        $this->assertSame('1734517', $this->passwordGenerator->generate());
    }
}
