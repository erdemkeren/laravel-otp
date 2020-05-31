<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp\PasswordGenerators;

use Erdemkeren\Otp\PasswordGeneratorInterface;
use Mockery as M;
use PHPUnit\Framework\TestCase;

if (! \function_exists('\Erdemkeren\Otp\PasswordGenerators\str_random')) {
    function str_random($l)
    {
        global $testerClass;

        return $testerClass::$functions->str_random($l);
    }
}

/** @covers \Erdemkeren\Otp\PasswordGenerators\StringPasswordGenerator */
class StringPasswordGeneratorTest extends TestCase
{
    /**
     * @var M\Mock
     */
    public static $functions;

    /**
     * @var PasswordGeneratorInterface
     */
    private $passwordGenerator;

    public function setUp()
    {
        self::$functions = M::mock();

        global $testerClass;
        $testerClass = self::class;

        $this->passwordGenerator = new StringPasswordGenerator();
    }

    public function tearDown()
    {
        M::close();
    }

    public function testGenerate()
    {
        $this::$functions->shouldReceive('str_random')
            ->once()->with(5)->andReturn('abcde');

        $password = $this->passwordGenerator->generate(5);
        $this->assertSame('abcde', $password);
    }
}
