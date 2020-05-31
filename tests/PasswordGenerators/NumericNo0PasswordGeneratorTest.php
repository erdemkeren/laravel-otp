<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp\PasswordGenerators;

use Erdemkeren\Otp\PasswordGeneratorInterface;
use Mockery as M;
use PHPUnit\Framework\TestCase;

if (! \function_exists('\Erdemkeren\Otp\PasswordGenerators\random_int')) {
    function random_int($min, $max)
    {
        global $testerClass;

        return $testerClass::$functions->random_int($min, $max);
    }
}

if (! \function_exists('\Erdemkeren\Otp\PasswordGenerators\rand')) {
    function rand($min, $max)
    {
        global $testerClass;

        return $testerClass::$functions->rand($min, $max);
    }
}

/** @covers \Erdemkeren\Otp\PasswordGenerators\NumericNo0PasswordGenerator */
class NumericNo0PasswordGeneratorTest extends TestCase
{
    /**
     * @var M\Mock
     */
    public static $functions;

    /**
     * @var PasswordGeneratorInterface
     */
    private $passwordGenerator;

    public function setUp(): void
    {
        self::$functions = M::mock();

        global $testerClass;
        $testerClass = self::class;

        $this->passwordGenerator = new NumericNo0PasswordGenerator();
    }

    public function tearDown(): void
    {
        M::close();
    }

    public function testGenerate(): void
    {
        $this::$functions->shouldReceive('random_int')
            ->once()->with(10000, 99999)->andReturn(10345);

        $this::$functions->shouldReceive('random_int')
            ->once()->with(1, 9)->andReturn(6);

        $password = $this->passwordGenerator->generate(5);
        $this->assertSame('16345', $password);
    }

    public function testGenerateUsesRandWhenRandomIntDontWork(): void
    {
        $this::$functions->shouldReceive('random_int')
            ->twice()->andThrow(\Exception::class);

        $this::$functions->shouldReceive('rand')
            ->once()->with(10000, 99999)->andReturn(10345);

        $this::$functions->shouldReceive('rand')
            ->once()->with(1, 9)->andReturn(7);

        $password = $this->passwordGenerator->generate(5);
        $this->assertSame('17345', $password);
    }
}
