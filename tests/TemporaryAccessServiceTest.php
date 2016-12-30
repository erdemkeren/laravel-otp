<?php

namespace Erdemkeren\TemporaryAccess\Tests;

use Mockery as M;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Erdemkeren\TemporaryAccess\Contracts\AccessCode;
use Erdemkeren\TemporaryAccess\Contracts\AccessToken;
use Erdemkeren\TemporaryAccess\TemporaryAccessService;
use Erdemkeren\TemporaryAccess\Contracts\AccessCodeGenerator;
use Erdemkeren\TemporaryAccess\Contracts\AccessTokenRepository;

class TemporaryAccessServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TemporaryAccessService
     */
    private $service;

    /**
     * @var AccessTokenRepository
     */
    private $repository;

    /**
     * @var AccessCodeGenerator
     */
    private $generator;

    /**
     * @var AccessCode
     */
    private $accessCode;

    /**
     * @var Authenticatable
     */
    private $authenticatable;

    /**
     * @var AccessToken
     */
    private $accessToken;

    /**
     * @var AccessToken
     */
    private $accessTokenClone;

    public function setUp()
    {
        $this->authenticatable = M::mock(Authenticatable::class);
        $this->repository = $repository = M::mock(AccessTokenRepository::class);
        $this->generator = $generator = M::mock(AccessCodeGenerator::class);
        $this->accessCode = M::mock(AccessCode::class);
        $this->accessToken = M::mock(AccessToken::class);
        $this->accessTokenClone = M::mock(AccessToken::class);
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
        $this->generator->shouldReceive('generate')->once()->andReturn($this->accessCode);
        $this->accessCode->shouldReceive('plain')->once()->andReturn('foo');
        $this->accessCode->shouldReceive('__toString')->once()->andReturn('bar');
        $this->repository->shouldReceive('store')->once()->with(1, "bar", null)->andReturn([
            'authenticatable_id' => 1,
            'id' => 1,
            'token' => 'bar',
            'created_at' => (string) Carbon::now(),
            'expires_at' => (string) Carbon::now(),
        ]);

        $accessToken = $this->service->generate($this->authenticatable);
        $this->assertInstanceOf(AccessToken::class, $accessToken);
    }

    /** @test */
    public function it_shall_generate_access_tokens_with_explicit_expire_dates()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->once()->andReturn(1);
        $this->generator->shouldReceive('generate')->once()->andReturn($this->accessCode);
        $this->accessCode->shouldReceive('plain')->once()->andReturn('foo');
        $this->accessCode->shouldReceive('__toString')->once()->andReturn('bar');
        $this->repository->shouldReceive('store')->once()->with(1, "bar", '2016-12-29 13:35:00')->andReturn([
            'authenticatable_id' => 1,
            'id' => 1,
            'token' => 'bar',
            'created_at' => (string) Carbon::now(),
            'expires_at' => (string) Carbon::now(),
        ]);

        Carbon::setTestNow(Carbon::create(2016, 12, 29, 13, 20));

        $accessToken = $this->service->generate($this->authenticatable, Carbon::now()->addMinutes(15));
        $this->assertInstanceOf(AccessToken::class, $accessToken);
    }

    /** @test */
    public function it_shall_update_access_tokens()
    {
        $this->accessToken->shouldReceive('authenticatableId')->once()->andReturn(1);
        $this->accessToken->shouldReceive('token')->once()->andReturn('foo');
        $this->accessToken->shouldReceive('expiresAt')->once()->andReturn('2016-12-29 13:35:00');
        $this->repository->shouldReceive('update')->once()->withArgs([1, 'foo', '2016-12-29 13:35:00'])->andReturn(1);

        $result = $this->service->update($this->accessToken);
        $this->assertEquals(true, $result);
    }

    /** @test */
    public function it_shall_revive_access_codes_from_plain_texts()
    {
        $this->generator->shouldReceive('fromPlain')->once()->andReturn($this->accessCode);

        $accessCode = $this->service->makeAccessCode('foo');

        $this->assertInstanceOf(AccessCode::class, $accessCode);
    }

    /** @test */
    public function it_shall_retrieve_an_access_token_by_access_code_using_code()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->accessCode->shouldReceive('encrypted')->andReturn('foo');

        $this->repository->shouldReceive('retrieve')->once()->with(1, 'foo')->andReturn($this->accessToken);

        $result = $this->service->retrieveByCode($this->authenticatable, $this->accessCode);
        $this->assertInstanceOf(AccessToken::class, $result);
    }

    /** @test */
    public function it_shall_retrieve_an_access_token_by_access_code_using_token()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->accessCode->shouldReceive('encrypted')->andReturn('foo');

        $this->repository->shouldReceive('retrieve')->once()->with(1, 'foo')->andReturn($this->accessToken);

        $result = $this->service->retrieveByToken($this->authenticatable, $this->accessCode);
        $this->assertInstanceOf(AccessToken::class, $result);
    }

    /** @test */
    public function it_shall_retrieve_an_access_token_from_plain_text()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->generator->shouldReceive('fromPlain')->once()->with('foo')->andReturn($this->accessCode);
        $this->accessCode->shouldReceive('encrypted')->andReturn('bar');

        $this->repository->shouldReceive('retrieve')->once()->with(1, 'bar')->andReturn($this->accessToken);

        $result = $this->service->retrieveByCode($this->authenticatable, 'foo');
        $this->assertInstanceOf(AccessToken::class, $result);
    }

    /** @test */
    public function it_shall_retrieve_an_access_token_from_encrypted_text()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);

        $this->repository->shouldReceive('retrieve')->once()->with(1, 'bar')->andReturn($this->accessToken);

        $result = $this->service->retrieveByToken($this->authenticatable, 'bar');
        $this->assertInstanceOf(AccessToken::class, $result);
    }

    /** @test */
    public function it_shall_determine_if_an_access_code_is_valid_by_code()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->accessToken->shouldReceive('plain')->andReturn('foo');
        $this->accessToken->shouldReceive('encrypted')->andReturn('bar');

        $this->repository->shouldReceive('retrieve')->once()->with(1, 'bar')->andReturn(true);

        $result = $this->service->checkCode($this->authenticatable, $this->accessToken);
        $this->assertEquals(true, $result);
    }

    /** @test */
    public function it_shall_determine_if_an_access_code_is_valid_by_token()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->accessToken->shouldReceive('plain')->andReturn('foo');
        $this->accessToken->shouldReceive('encrypted')->andReturn('bar');

        $this->repository->shouldReceive('retrieve')->once()->with(1, 'bar')->andReturn(true);

        $result = $this->service->checkToken($this->authenticatable, $this->accessToken);
        $this->assertEquals(true, $result);
    }

    /** @test */
    public function it_shall_determine_the_access_token_does_not_exist_from_plain_text_and_return_false()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->generator->shouldReceive('fromPlain')->once()->andReturn($this->accessCode);
        $this->accessCode->shouldReceive('encrypted')->once()->andReturn('foo');
        $this->repository->shouldReceive('retrieve')->once()->with(1, 'foo')->andReturn(null);

        $result = $this->service->checkCodeAndProlong($this->authenticatable, 'foo', 5);
        $this->assertFalse($result);
    }

    /** @test */
    public function it_shall_determine_the_access_token_does_not_exist_and_return_false()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->accessCode->shouldReceive('encrypted')->once()->andReturn('foo');
        $this->repository->shouldReceive('retrieve')->once()->with(1, 'foo')->andReturn(null);

        $result = $this->service->checkCodeAndProlong($this->authenticatable, $this->accessCode, 5);
        $this->assertFalse($result);
    }

    /** @test */
    public function it_shall_determine_the_access_token_does_not_exist_by_token_and_return_false()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->accessCode->shouldReceive('encrypted')->once()->andReturn('foo');
        $this->repository->shouldReceive('retrieve')->once()->with(1, 'foo')->andReturn(null);

        $result = $this->service->checkTokenAndProlong($this->authenticatable, $this->accessCode, 5);
        $this->assertFalse($result);
    }

    /** @test */
    public function it_shall_determine_if_an_access_token_exists_and_prolong_the_expire_time()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->accessCode->shouldReceive('encrypted')->once()->andReturn('foo');
        $this->repository->shouldReceive('retrieve')->once()->andReturn((object) [
            'id' => 1,
            'authenticatable_id' => 1,
            'token' => 'foo',
            'created_at' => '2016-12-29 16:35:00',
            'expires_at' => '2016-12-29 16:50:00',
        ]);
        $this->repository->shouldReceive('update')->once()->with(1, 'foo', '2016-12-29 16:55:00')->andReturn(true);

        $result = $this->service->checkCodeAndProlong($this->authenticatable, $this->accessCode, 5);
        $this->assertInstanceOf(AccessToken::class, $result);
    }

    /** @test */
    public function it_shall_determine_if_an_access_token_exists_with_plaintext_and_prolong_the_expire_time()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->generator->shouldReceive('fromPlain')->once()->with('foo')->andReturn($this->accessCode);
        $this->accessCode->shouldReceive('encrypted')->once()->andReturn('bar');
        $this->repository->shouldReceive('retrieve')->once()->andReturn((object) [
            'id' => 1,
            'authenticatable_id' => 1,
            'token' => 'bar',
            'created_at' => '2016-12-29 16:35:00',
            'expires_at' => '2016-12-29 16:50:00',
        ]);
        $this->repository->shouldReceive('update')->once()->with(1, 'bar', '2016-12-29 16:55:00')->andReturn(true);

        $result = $this->service->checkCodeAndProlong($this->authenticatable, 'foo', 5);
        $this->assertInstanceOf(AccessToken::class, $result);
    }

    /** @test */
    public function it_shall_determine_if_an_access_token_exists_and_prolong_the_expire_time_with_the_lost_time()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->accessCode->shouldReceive('encrypted')->once()->andReturn('foo');

        Carbon::setTestNow(Carbon::create(2016, 12, 29, 16, 40));
        $this->repository->shouldReceive('retrieve')->once()->andReturn((object) [
            'id' => 1,
            'authenticatable_id' => 1,
            'token' => 'foo',
            'created_at' => '2016-12-29 16:35:00',
            'expires_at' => '2016-12-29 16:50:00',
        ]);
        $this->repository->shouldReceive('update')->once()->with(1, 'foo', '2016-12-29 16:55:00')->andReturn(true);

        $result = $this->service->checkCodeAndProlong($this->authenticatable, $this->accessCode);
        $this->assertInstanceOf(AccessToken::class, $result);
    }

    /** @test */
    public function it_shall_determine_if_an_access_token_exists_by_encrypted_text_and_prolong_the_expire_time_with_the_lost_time()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);

        Carbon::setTestNow(Carbon::create(2016, 12, 29, 16, 40));
        $this->repository->shouldReceive('retrieve')->once()->andReturn((object) [
            'id' => 1,
            'authenticatable_id' => 1,
            'token' => 'foo',
            'created_at' => '2016-12-29 16:35:00',
            'expires_at' => '2016-12-29 16:50:00',
        ]);
        $this->repository->shouldReceive('update')->once()->with(1, 'foo', '2016-12-29 16:55:00')->andReturn(true);

        $result = $this->service->checkTokenAndProlong($this->authenticatable, 'foo');
        $this->assertInstanceOf(AccessToken::class, $result);
    }

    /** @test */
    public function it_shall_return_false_if_the_update_was_unsuccessful()
    {
        $this->authenticatable->shouldReceive('getAuthIdentifier')->andReturn(1);
        $this->accessCode->shouldReceive('encrypted')->once()->andReturn('foo');
        $this->repository->shouldReceive('retrieve')->once()->andReturn((object) [
            'id' => 1,
            'authenticatable_id' => 1,
            'token' => 'foo',
            'created_at' => '2016-12-29 16:35:00',
            'expires_at' => '2016-12-29 16:50:00',
        ]);
        $this->repository->shouldReceive('update')->once()->with(1, 'foo', '2016-12-29 16:55:00')->andReturn(false);

        $result = $this->service->checkCodeAndProlong($this->authenticatable, $this->accessCode, 5);
        $this->assertFalse($result);
    }

    /** @test */
    public function it_shall_retrieve_access_tokens_by_attributes()
    {
        $this->repository->shouldReceive('retrieveByAttributes')->once()->with([
            'authenticatable_id' => 1,
            'token'              => 'foo'
        ], [
            'token'
        ])->andReturn($this->accessToken);

        $accessToken = $this->service->retrieveByAttributes(['authenticatable_id' => 1, 'token' => 'foo'], ['token']);

        $this->assertInstanceOf(AccessToken::class, $accessToken);
    }

    /** @test */
    public function it_shall_return_null_if_it_cant_retrieve_token_by_attributes()
    {
        $this->repository->shouldReceive('retrieveByAttributes')->once()->with([
            'authenticatable_id' => 1,
            'token'              => 'foo'
        ], [
            'token'
        ])->andReturn(null);

        $accessToken = $this->service->retrieveByAttributes(['authenticatable_id' => 1, 'token' => 'foo'], ['token']);

        $this->assertNull($accessToken);
    }

    /** @test */
    public function it_shall_delete_access_tokens()
    {
        $this->accessToken->shouldReceive('authenticatableId')->once()->andReturn(1);
        $this->accessToken->shouldReceive('token')->once()->andReturn('foo');
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
