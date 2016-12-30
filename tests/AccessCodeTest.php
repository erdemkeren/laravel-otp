<?php

namespace Erdemkeren\TemporaryAccess\Test;

use Erdemkeren\TemporaryAccess\AccessCode;

class AccessCodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AccessCode
     */
    private $accessCode;

    public function setUp()
    {
        $this->accessCode = $accessCode = new AccessCode('foo', 'bar');

        parent::setUp();
    }

    /** @test */
    public function it_shall_provide_the_plain_text()
    {
        $this->assertEquals($this->accessCode->plain(), 'foo');
    }

    /** @test */
    public function it_shall_provide_the_plain_text_with_alias_code()
    {
        $this->assertEquals($this->accessCode->code(), 'foo');
    }

    /** @test */
    public function it_shall_provide_the_code_encrypted()
    {
        $this->assertEquals($this->accessCode->encrypted(), 'bar');
    }

    /** @test */
    public function it_shall_provide_the_code_with_alias_token()
    {
        $this->assertEquals($this->accessCode->token(), 'bar');
    }

    /** @test */
    public function it_shall_convert_to_encrypted_text_when_forced_to_be_string()
    {
        $this->assertEquals((string) $this->accessCode, 'bar');
    }
}
