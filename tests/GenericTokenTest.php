<?php

namespace Erdemkeren\TemporaryAccess\Test;

use Erdemkeren\TemporaryAccess\GenericToken;

class GenericTokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GenericToken
     */
    private $token;

    public function setUp()
    {
        $this->token = $token = new GenericToken('bar', 'foo');

        parent::setUp();
    }

    /** @test */
    public function it_shall_provide_the_token_as_plain_text()
    {
        $this->assertEquals($this->token->plain(), 'foo');
    }

    /** @test */
    public function it_shall_provide_the_token_as_encrypted_text()
    {
        $this->assertEquals($this->token->encrypted(), 'bar');
    }

    /** @test */
    public function it_shall_convert_to_encrypted_text_when_forced_to_be_string()
    {
        $this->assertEquals((string) $this->token, 'bar');
    }
}
