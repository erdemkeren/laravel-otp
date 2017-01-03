<?php

namespace Erdemkeren\TemporaryAccess\Tests;

use Carbon\Carbon;
use Erdemkeren\TemporaryAccess\Contracts\AccessTokenInterface;
use Erdemkeren\TemporaryAccess\Contracts\AccessTokenRepositoryInterface;
use Erdemkeren\TemporaryAccess\Contracts\TokenGeneratorInterface;
use Erdemkeren\TemporaryAccess\Contracts\TokenInterface;
use Erdemkeren\TemporaryAccess\TemporaryAccessService;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Mockery as M;

class TemporaryAccessServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TemporaryAccessService
     */
    private $service;

    /**
     * @var AccessTokenRepositoryInterface
     */
    private $repository;

    /**
     * @var TokenGeneratorInterface
     */
    private $generator;

    /**
     * @var TokenInterface
     */
    private $token;

    /**
     * @var AuthenticatableContract
     */
    private $authenticatable;

    /**
     * @var AccessTokenInterface
     */
    private $accessToken;

    /**
     * @var AccessTokenInterface
     */
    private $accessTokenClone;

    public function setUp()
    {
        $this->authenticatable = M::mock(AuthenticatableContract::class);
        $this->repository = $repository = M::mock(AccessTokenRepositoryInterface::class);
        $this->generator = $generator = M::mock(TokenGeneratorInterface::class);
        $this->token = M::mock(TokenInterface::class);
        $this->accessToken = M::mock(AccessTokenInterface::class);
        $this->accessTokenClone = M::mock(AccessTokenInterface::class);
        $this->service = new TemporaryAccessService($repository, $generator);
    }

    public function tearDown()
    {
        M::close();

        parent::tearDown();
    }

    /** @test */
    public function it_shall_generate_access_tokens_with_default_expire_dates()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->once()->andReturn(1);
        $this->generator->shouldReceive('generate')->once()->andReturn($this->token);
        $this->token->shouldReceive('__toString')->once()->andReturn('bar');
        
        $this->repository->shouldReceive('store')->once()->with(1, 'bar', null)->andReturn((object) [
            'authenticatable_id' => 1,
            'id'                 => 1,
            'token'              => 'bar',
            'created_at'         => (string) $createdAt = Carbon::now(),
            'expires_at'         => (string) $expiresAt = Carbon::now(),
        ]);

        $accessToken = $this->service->generate($this->authenticatable);
        $this->assertInstanceOf(AccessTokenInterface::class, $accessToken);
    }

    /** @test */
    public function it_shall_generate_access_tokens_with_explicit_expire_dates()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->once()->andReturn(1);
        $this->generator->shouldReceive('generate')->once()->andReturn($this->token);
        $this->token->shouldReceive('__toString')->once()->andReturn('bar');
        
        $this->repository->shouldReceive('store')->once()->with(1, 'bar', '2016-12-29 13:35:00')->andReturn((object) [
            'authenticatable_id' => 1,
            'id'                 => 1,
            'token'              => 'bar',
            'created_at'         => (string) Carbon::now(),
            'expires_at'         => (string) Carbon::now(),
        ]);

        Carbon::setTestNow(Carbon::create(2016, 12, 29, 13, 20));

        $accessToken = $this->service->generate($this->authenticatable, Carbon::now()->addMinutes(15));
        $this->assertInstanceOf(AccessTokenInterface::class, $accessToken);
    }

    /** @test */
    public function it_shall_update_access_tokens()
    {
        $this->accessToken->shouldReceive('authenticatableId')->once()->andReturn(1);
        $this->accessToken->shouldReceive('__toString')->once()->andReturn('foo');
        $this->accessToken->shouldReceive('expiresAt')->once()->andReturn('2016-12-29 13:35:00');
        $this->repository->shouldReceive('update')->once()->withArgs([1, 'foo', '2016-12-29 13:35:00'])->andReturn(1);

        $result = $this->service->update($this->accessToken);
        $this->assertEquals(true, $result);
    }

    /** @test */
    public function it_shall_revive_tokens_from_plain_texts()
    {
        $this->generator->shouldReceive('fromPlain')->once()->andReturn($this->token);

        $token = $this->service->makeTokenFromPlainText('foo');

        $this->assertInstanceOf(TokenInterface::class, $token);
    }

    /** @test */
    public function it_shall_retrieve_an_access_token()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->generator->shouldReceive('fromEncrypted')->once()->with('bar')->andReturn($this->token);
        $this->token->shouldReceive('__toString')->andReturn('foo');

        $this->repository->shouldReceive('retrieve')->once()->with(1, 'foo')->andReturn((object) [
            'authenticatable_id' => 1,
            'id'                 => 1,
            'token'              => 'bar',
            'created_at'         => (string) Carbon::now(),
            'expires_at'         => (string) Carbon::now(),
        ]);

        $result = $this->service->retrieve($this->authenticatable, $this->token);
        $this->assertInstanceOf(AccessTokenInterface::class, $result);
    }

    /** @test */
    public function it_shall_return_null_if_it_cant_retrieve_an_access_token()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->token->shouldReceive('__toString')->andReturn('foo');

        $this->repository->shouldReceive('retrieve')->once()->with(1, 'foo')->andReturn(null);

        $result = $this->service->retrieve($this->authenticatable, $this->token);
        $this->assertNull($result);
    }

    /** @test */
    public function it_shall_retrieve_an_access_token_from_plain_text()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->generator->shouldReceive('fromPlain')->once()->with('foo')->andReturn($this->token);
        $this->token->shouldReceive('__toString')->andReturn('bar');

        $this->repository->shouldReceive('retrieve')->once()->with(1, 'bar')->andReturn((object) [
            'authenticatable_id' => 1,
            'id'                 => 1,
            'token'              => 'bar',
            'created_at'         => (string) Carbon::now(),
            'expires_at'         => (string) Carbon::now(),
        ]);

        $this->generator->shouldReceive('fromEncrypted')->once()->with('bar')->andReturn($this->token);

        $result = $this->service->retrieveUsingPlainText($this->authenticatable, 'foo');
        $this->assertInstanceOf(AccessTokenInterface::class, $result);
    }

    /** @test */
    public function it_shall_retrieve_an_access_token_from_encrypted_text()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);

        $this->repository->shouldReceive('retrieve')->once()->with(1, 'bar')->andReturn((object) [
            'authenticatable_id' => 1,
            'id'                 => 1,
            'token'              => 'bar',
            'created_at'         => (string) Carbon::now(),
            'expires_at'         => (string) Carbon::now(),
        ]);

        $this->generator->shouldReceive('fromEncrypted')->once()->with('bar')->andReturn($this->token);

        $result = $this->service->retrieve($this->authenticatable, 'bar');
        $this->assertInstanceOf(AccessTokenInterface::class, $result);
    }

    /** @test */
    public function it_shall_determine_if_an_access_token_is_valid_using_plain_text()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->generator->shouldReceive('fromPlain')->once()->andReturn($this->token);
        $this->token->shouldReceive('__toString')->once()->andReturn('bar');

        $this->repository->shouldReceive('retrieve')->once()->with(1, 'bar')->andReturn((object) [
            'authenticatable_id' => 1,
            'id'                 => 1,
            'token'              => 'bar',
            'created_at'         => (string) Carbon::now(),
            'expires_at'         => (string) Carbon::now(),
        ]);

        $this->generator->shouldReceive('fromEncrypted')->once()->andReturn($this->token);

        $result = $this->service->checkUsingPlainText($this->authenticatable, $this->accessToken);
        $this->assertEquals(true, $result);
    }

    /** @test */
    public function it_shall_determine_if_an_access_token_is_valid_by_encrypted_text()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->generator->shouldReceive('fromEncrypted')->once()->andReturn($this->token);

        $this->repository->shouldReceive('retrieve')->once()->with(1, 'bar')->andReturn((object) [
            'authenticatable_id' => 1,
            'id'                 => 1,
            'token'              => 'bar',
            'created_at'         => (string) Carbon::now(),
            'expires_at'         => (string) Carbon::now(),
        ]);

        $result = $this->service->check($this->authenticatable, 'bar');
        $this->assertEquals(true, $result);
    }

    /** @test */
    public function it_shall_determine_if_an_access_token_is_valid()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->accessToken->shouldReceive('__toString')->once()->andReturn('bar');

        $this->repository->shouldReceive('retrieve')->once()->with(1, 'bar')->andReturn((object) [
            'authenticatable_id' => 1,
            'id'                 => 1,
            'token'              => 'bar',
            'created_at'         => (string) Carbon::now(),
            'expires_at'         => (string) Carbon::now(),
        ]);

        $this->generator->shouldReceive('fromEncrypted')->once()->andReturn($this->token);

        $result = $this->service->check($this->authenticatable, $this->accessToken);
        $this->assertEquals(true, $result);
    }

    /** @test */
    public function it_shall_determine_the_access_token_does_not_exist_from_plain_text_and_return_false()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->generator->shouldReceive('fromPlain')->once()->andReturn($this->token);
        $this->token->shouldReceive('__toString')->once()->andReturn('foo');
        $this->repository->shouldReceive('retrieve')->once()->with(1, 'foo')->andReturn(null);

        $result = $this->service->checkUsingPlainText($this->authenticatable, 'foo', 5);
        $this->assertFalse($result);
    }

    /** @test */
    public function it_shall_determine_the_access_token_does_not_exist_and_return_false()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->accessToken->shouldReceive('__toString')->once()->andReturn('foo');
        $this->repository->shouldReceive('retrieve')->once()->with(1, 'foo')->andReturn(null);

        $result = $this->service->check($this->authenticatable, $this->accessToken, 5);
        $this->assertFalse($result);
    }

    /** @test */
    public function it_shall_determine_the_access_token_does_not_exist_by_access_token_and_return_false()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->repository->shouldReceive('retrieve')->once()->with(1, 'bar')->andReturn(null);

        $result = $this->service->check($this->authenticatable, 'bar', 5);
        $this->assertFalse($result);
    }

    /** @test */
    public function it_shall_determine_if_an_access_token_exists_and_prolong_the_expire_time()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->accessToken->shouldReceive('__toString')->andReturn('foo');

        $this->repository->shouldReceive('retrieve')->once()->andReturn((object) [
            'id'                 => 1,
            'authenticatable_id' => 1,
            'token'              => 'foo',
            'created_at'         => '2016-12-29 16:35:00',
            'expires_at'         => '2016-12-29 16:50:00',
        ]);

        $this->generator->shouldReceive('fromEncrypted')->once()->andReturn($this->token);
        $this->token->shouldReceive('__toString')->once()->andReturn('foo');
        $this->repository->shouldReceive('update')->once()->with(1, 'foo', '2016-12-29 16:55:00')->andReturn(true);


        $result = $this->service->checkAndProlong($this->authenticatable, $this->accessToken, 5);
        $this->assertInstanceOf(AccessTokenInterface::class, $result);
    }

    /** @test */
    public function it_shall_determine_if_an_access_token_does_not_exist_and_return_false()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);

        $this->repository->shouldReceive('retrieve')->once()->with(1, 'bar')->andReturn(null);

        $result = $this->service->checkAndProlong($this->authenticatable, 'bar', 5);
        $this->assertFalse($result);
    }

    /** @test */
    public function it_shall_determine_if_an_access_token_exists_with_plaintext_and_prolong_the_expire_time()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->generator->shouldReceive('fromPlain')->once()->with('foo')->andReturn($this->token);
        $this->token->shouldReceive('__toString')->once()->andReturn('bar');
        $this->repository->shouldReceive('retrieve')->once()->andReturn((object) [
            'id'                 => 1,
            'authenticatable_id' => 1,
            'token'              => 'bar',
            'created_at'         => '2016-12-29 16:35:00',
            'expires_at'         => '2016-12-29 16:50:00',
        ]);

        $this->generator->shouldReceive('fromEncrypted')->once()->with('bar')->andReturn($this->token);
        $this->token->shouldReceive('__toString')->once()->andReturn('bar');
        $this->repository->shouldReceive('update')->once()->with(1, 'bar', '2016-12-29 16:55:00')->andReturn(true);

        $result = $this->service->checkUsingPlainTextAndProlong($this->authenticatable, 'foo', 5);
        $this->assertInstanceOf(AccessTokenInterface::class, $result);
    }

    /** @test */
    public function it_shall_determine_if_an_access_token_exists_and_prolong_the_expire_time_with_the_lost_time()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->accessToken->shouldReceive('__toString')->once()->andReturn('foo');

        Carbon::setTestNow(Carbon::create(2016, 12, 29, 16, 40));
        $this->repository->shouldReceive('retrieve')->once()->andReturn((object) [
            'id'                 => 1,
            'authenticatable_id' => 1,
            'token'              => 'foo',
            'created_at'         => '2016-12-29 16:35:00',
            'expires_at'         => '2016-12-29 16:50:00',
        ]);

        $this->generator->shouldReceive('fromEncrypted')->once()->andReturn($this->token);
        $this->token->shouldReceive('__toString')->once()->andReturn('foo');
        $this->repository->shouldReceive('update')->once()->with(1, 'foo', '2016-12-29 16:55:00')->andReturn(true);

        $result = $this->service->checkAndProlong($this->authenticatable, $this->accessToken);
        $this->assertInstanceOf(AccessTokenInterface::class, $result);
    }

    /** @test */
    public function it_shall_determine_if_an_access_token_exists_by_encrypted_text_and_prolong_the_expire_time_with_the_lost_time()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);

        Carbon::setTestNow(Carbon::create(2016, 12, 29, 16, 40));
        $this->repository->shouldReceive('retrieve')->once()->andReturn((object) [
            'id'                 => 1,
            'authenticatable_id' => 1,
            'token'              => 'foo',
            'created_at'         => '2016-12-29 16:35:00',
            'expires_at'         => '2016-12-29 16:50:00',
        ]);

        $this->generator->shouldReceive('fromEncrypted')->once()->andReturn($this->token);
        $this->token->shouldReceive('__toString')->once()->andReturn('foo');
        $this->repository->shouldReceive('update')->once()->with(1, 'foo', '2016-12-29 16:55:00')->andReturn(true);

        $result = $this->service->checkAndProlong($this->authenticatable, 'foo');
        $this->assertInstanceOf(AccessTokenInterface::class, $result);
    }

    /** @test */
    public function it_shall_return_false_if_the_update_was_unsuccessful()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);

        $this->accessToken->shouldReceive('__toString')->once()->andReturn('foo');
        $this->repository->shouldReceive('retrieve')->once()->andReturn((object) [
            'id'                 => 1,
            'authenticatable_id' => 1,
            'token'              => 'foo',
            'created_at'         => '2016-12-29 16:35:00',
            'expires_at'         => '2016-12-29 16:50:00',
        ]);

        $this->generator->shouldReceive('fromEncrypted')->with('foo')->once()->andReturn($this->token);
        $this->token->shouldReceive('__toString')->once()->andReturn('foo');
        $this->repository->shouldReceive('update')->once()->with(1, 'foo', '2016-12-29 16:55:00')->andReturn(false);

        $result = $this->service->checkAndProlong($this->authenticatable, $this->accessToken, 5);
        $this->assertFalse($result);
    }

    /** @test */
    public function it_shall_retrieve_access_tokens_by_attributes()
    {
        $this->repository->shouldReceive('retrieveByAttributes')->once()->with([
            'authenticatable_id' => 1,
            'token'              => 'foo',
        ], [
            'token',
        ])->andReturn((object) [
            'id'                 => 1,
            'authenticatable_id' => 1,
            'token'              => 'foo',
            'created_at'         => '2016-12-29 16:35:00',
            'expires_at'         => '2016-12-29 16:50:00',
        ]);

        $this->generator->shouldReceive('fromEncrypted')->once()->andReturn($this->token);

        $accessToken = $this->service->retrieveByAttributes(['authenticatable_id' => 1, 'token' => 'foo'], ['token']);

        $this->assertInstanceOf(AccessTokenInterface::class, $accessToken);
    }

    /** @test */
    public function it_shall_return_null_if_it_cant_retrieve_token_by_attributes()
    {
        $this->repository->shouldReceive('retrieveByAttributes')->once()->with([
            'authenticatable_id' => 1,
            'token'              => 'foo',
        ], [
            'token',
        ])->andReturn(null);

        $accessToken = $this->service->retrieveByAttributes(['authenticatable_id' => 1, 'token' => 'foo'], ['token']);

        $this->assertNull($accessToken);
    }

    public function it_shall_create_access_tokens_by_plain_texts()
    {

    }

    /** @test */
    public function it_shall_delete_access_tokens()
    {
        $this->accessToken->shouldReceive('authenticatableId')->once()->andReturn(1);
        $this->accessToken->shouldReceive('__toString')->once()->andReturn('foo');
        $this->repository->shouldReceive('delete')->once()->with(1, 'foo')->andReturn(1);

        $result = $this->service->delete($this->accessToken);
        $this->assertTrue($result);
    }

    /** @test */
    public function it_shall_delete_expired_access_tokens()
    {
        $this->repository->shouldReceive('deleteExpired')->once();

        $this->service->deleteExpired();
    }
}
