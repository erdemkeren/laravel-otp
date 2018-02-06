<?php

namespace Erdemkeren\TemporaryAccess\Token\TokenGenerator;

use Erdemkeren\TemporaryAccess\Token\TokenInterface;
use Erdemkeren\TemporaryAccess\Contracts\TokenGeneratorInterface;

use \Mockery;

function random_int ($min, $max) {
    return NumericNo0TokenGeneratorTest::$functions->random_int($min, $max);
}

class NumericNo0TokenGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public static $functions;

    /**
     * @var TokenGenerator
     */
    private $tokenGenerator;

    public function setUp()
    {
        self::$functions = Mockery::mock();

        $this->tokenGenerator = new NumericNo0TokenGenerator('key', 6);
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function it_shall_be_an_instance_of_token_generator_contract()
    {
        $this->assertInstanceOf(TokenGeneratorInterface::class, $this->tokenGenerator);
    }

    /** @test */
    public function it_shall_generate_new_tokens()
    {
        $token = $this->tokenGenerator->generate();

        $this->assertInstanceOf(TokenInterface::class, $token);

        $this->assertEquals(6, strlen($token->plain()));
        $this->assertEquals(64, strlen($token->encrypted()));
    }

    /** @test */
    public function it_shall_generate_less_secure_tokens_even_if_random_int_throws_exceptions()
    {
        self::$functions->shouldReceive('random_int')->with(100000, 999999)->once()->andThrow(\Exception::class);
        self::$functions->shouldReceive('random_int')->with(1, 9)->once()->andThrow(\Exception::class);

        $token = (new NumericNo0TokenGenerator('key', 6))->generate();

        $this->assertInstanceOf(TokenInterface::class, $token);

        $this->assertEquals(6, strlen($token->plain()));
        $this->assertEquals(64, strlen($token->encrypted()));
    }

    /** @test */
    public function it_shall_generate_tokens_from_plain_texts()
    {
        $plainText = 'foo';

        $token = $this->tokenGenerator->fromPlain($plainText);

        $this->assertEquals(3, strlen($token->plain()));
        $this->assertEquals(64, strlen($token->encrypted()));
    }

    /** @test */
    public function it_shall_generate_tokens_from_encrypted_text()
    {
        $plainText = 'foo';

        $token = $this->tokenGenerator->fromEncrypted($plainText);

        $this->assertNull($token->plain());
        $this->assertEquals(3, strlen($token->encrypted()));
    }
}
