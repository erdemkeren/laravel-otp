<?php

namespace Erdemkeren\TemporaryAccess\Test;

use Mockery as M;
use Carbon\Carbon;
use Erdemkeren\TemporaryAccess\GenericAccessToken;
use Erdemkeren\TemporaryAccess\Token\TokenInterface;
use Erdemkeren\TemporaryAccess\Contracts\AccessTokenInterface;

class GenericAccessTokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AccessTokenInterface
     */
    private $accessToken;

    /**
     * @var TokenInterface
     */
    private $token;

    /**
     * @var array
     */
    private $accessTokenAttributes;

    public function setUp()
    {
        $this->accessTokenAttributes = $accessTokenAttributes = [
            'id'                 => 1,
            'authenticatable_id' => 1,
            'plain'              => 'foo',
            'token'              => 'bar',
            'created_at'         => '2016-12-28 19:35:27',
            'expires_at'         => '2016-12-28 19:50:27',
        ];

        $this->token = $token = M::mock(TokenInterface::class);

        $this->accessToken = new GenericAccessToken($token, $accessTokenAttributes);

        parent::setUp();
    }

    public function tearDown()
    {
        M::close();

        parent::tearDown();
    }

    /** @test */
    public function it_shall_be_an_instance_of_access_token_contract()
    {
        $this->assertInstanceOf(AccessTokenInterface::class, $this->accessToken);
    }

    /** @test */
    public function it_shall_provide_the_unique_authenticatable_identifier()
    {
        $this->assertEquals($this->accessToken->authenticatableId(), $this->accessTokenAttributes['authenticatable_id']);
    }

    /** @test */
    public function it_shall_provide_the_token_as_plain_text()
    {
        $this->token->shouldReceive('plain')->once()->andReturn('foo');

        $this->assertEquals($this->accessToken->plain(), 'foo');
    }

    /** @test */
    public function it_shall_throw_exception_when_no_plain_is_available()
    {
        $this->token->shouldReceive('plain')->once()->andReturn(null);

        $this->setExpectedException(\LogicException::class);

        $this->accessToken->plain();
    }

    /** @test */
    public function it_shall_provide_the_token()
    {
        $this->assertEquals($this->accessToken->token(), $this->token);
    }

    /** @test */
    public function it_shall_provide_the_token_as_encrypted_text()
    {
        $this->token->shouldReceive('__toString')->once()->andReturn('bar');

        $this->assertEquals($this->accessToken->encrypted(), 'bar');
    }

    /** @test */
    public function it_shall_provide_the_date_access_token_created()
    {
        $this->assertInstanceOf(Carbon::class, $this->accessToken->createdAt());
        $this->assertTrue((new Carbon($this->accessTokenAttributes['created_at']))->eq($this->accessToken->createdAt()));
    }

    /** @test */
    public function it_shall_return_a_new_access_token_instance_when_prolong_called()
    {
        $accessToken = $this->accessToken->prolong(300);

        $this->assertNotSame($this->accessToken, $accessToken);
        $this->assertSame('2016-12-28 19:55:27', (string) $accessToken->expiresAt());
    }
}
