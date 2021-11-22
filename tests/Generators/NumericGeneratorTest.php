<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp\Generators;

use function function_exists;

if (! function_exists('\Erdemkeren\Otp\PasswordGenerators\random_int')) {
    function random_int($min, $max)
    {
        global $testerClass;

        return $testerClass::$functions->random_int($min, $max);
    }
}

if (! function_exists('\Erdemkeren\Otp\PasswordGenerators\rand')) {
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
use Erdemkeren\Otp\Generators\NumericGenerator;

/**
 * @covers \Erdemkeren\Otp\Generators\NumericGenerator
 */
class NumericGeneratorTest extends TestCase
{
    public static M\MockInterface $functions;

    private NumericGenerator $passwordGenerator;

    public function setUp(): void
    {
        self::$functions = M::mock();

        global $testerClass;
        $testerClass = self::class;

        $this->passwordGenerator = new NumericGenerator();
    }

    public function tearDown(): void
    {
        M::close();
    }

    /**
     * @test
     */
    public function itGeneratesRandomNumericTokens(): void
    {
        $this::$functions->shouldReceive('random_int')
            ->once()
            ->with(10000000, 99999999)
            ->andReturn(10345310);

        $this->assertSame('10345310', $this->passwordGenerator->generate());
    }

    /**
     * @test
     */
    public function itUsesRandWhenRandomIntDontWork(): void
    {
        $this::$functions->shouldReceive('random_int')
            ->once()
            ->andThrow(Exception::class);

        $this::$functions->shouldReceive('rand')
            ->once()
            ->with(10000000, 99999999)
            ->andReturn(10345310);

        $this->assertSame('10345310', $this->passwordGenerator->generate());
    }
}
