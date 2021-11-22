<?php

/*
 * @copyright 20221 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp\Test\Generators;

use PHPUnit\Framework\TestCase;
use Erdemkeren\Otp\Generators\StringGenerator;

/**
 * @covers \Erdemkeren\Otp\StringGenerator
 */
class StringGeneratorTest extends TestCase
{
    private StringGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = new StringGenerator();
    }

    /**
     * @test
     */
    public function itGenerates(): void
    {
        $this->assertSame(8, strlen($this->generator->generate()));
    }
}
