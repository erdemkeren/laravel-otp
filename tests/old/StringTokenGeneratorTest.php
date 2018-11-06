<?php

namespace Erdemkeren\TemporaryAccess\Tests;

use Erdemkeren\TemporaryAccess\Token\TokenInterface;
use Erdemkeren\TemporaryAccess\Contracts\TokenGeneratorInterface;
use Erdemkeren\TemporaryAccess\Token\TokenGenerator\StringPasswordGenerator;

class StringTokenGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TokenGenerator
     */
    private $tokenGenerator;

    public function setUp()
    {
        $this->tokenGenerator = new StringPasswordGenerator('key', 6);
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
