<?php

namespace Erdemkeren\TemporaryAccess\Tests;

use Mockery as M;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\ConnectionInterface;
use Erdemkeren\TemporaryAccess\DatabaseAccessTokenRepository;
use Erdemkeren\TemporaryAccess\Contracts\AccessTokenRepositoryInterface;

class DatabaseAccessTokenRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * @var Builder
     */
    private $builder;

    /**
     * @var AccessTokenRepositoryInterface
     */
    private $accessTokenRepository;

    public function setUp()
    {
        $this->connection = $connection = M::mock(ConnectionInterface::class);
        $this->builder = M::mock(Builder::class);

        $this->accessTokenRepository = new DatabaseAccessTokenRepository($connection, 'table_name', 15);

        parent::setUp();
    }

    public function tearDown()
    {
        M::close();

        parent::tearDown();
    }

    /** @test */
    public function it_shall_be_an_instance_of_access_token_repository()
    {
        $this->assertInstanceOf(AccessTokenRepositoryInterface::class, $this->accessTokenRepository);
    }

    /** @test */
    public function it_shall_determine_if_an_access_token_is_not_expired()
    {
        $this->connection->shouldReceive('table')->once()->andReturn($this->builder);

        $this->builder->shouldReceive('where')->twice()->andReturn($this->builder);

        Carbon::setTestNow(Carbon::create(2016, 12, 28, 23, 15, 00));

        $this->builder->shouldReceive('first')->once()->andReturn((object) [
            'id'         => 1,
            'token'      => 'foo',
            'created_at' => '2016-12-28 23:00:00',
            'expires_at' => '2016-12-28 23:15:01',
        ]);

        $accessToken = $this->accessTokenRepository->retrieve(1, 'foo');
        $this->assertNotNull($accessToken);
    }

    /** @test */
    public function it_shall_determine_if_an_access_token_is_expired()
    {
        $this->connection->shouldReceive('table')->once()->andReturn($this->builder);

        $this->builder->shouldReceive('where')->twice()->andReturn($this->builder);

        Carbon::setTestNow(Carbon::create(2016, 12, 28, 23, 15, 01));

        $this->builder->shouldReceive('first')->once()->andReturn((object) [
            'id'         => 1,
            'token'      => 'foo',
            'created_at' => '2016-12-28 23:00:00',
            'expires_at' => '2016-12-28 23:15:01',
        ]);

        $this->assertNull($this->accessTokenRepository->retrieve(1, 'foo'));
    }

    /** @test */
    public function it_shall_store_new_access_tokens()
    {
        $this->connection->shouldReceive('table')->once()->andReturn($this->builder);

        Carbon::setTestNow(Carbon::create(2016, 12, 28, 23));

        $this->builder->shouldReceive('insertGetId')->once()->with([
            'authenticatable_id' => 1,
            'token'              => 'foo',
            'created_at'         => '2016-12-28 23:00:00',
            'expires_at'         => '2016-12-28 23:15:00',
        ])->andReturn(1);

        $result = $this->accessTokenRepository->store(1, 'foo');
        $this->assertEquals([
            'id'                 => 1,
            'authenticatable_id' => 1,
            'token'              => 'foo',
            'created_at'         => '2016-12-28 23:00:00',
            'expires_at'         => '2016-12-28 23:15:00',
        ], $result);
    }

    /** @test */
    public function it_shall_update_an_access_token_in_the_storage()
    {
        $this->connection->shouldReceive('table')->once()->andReturn($this->builder);

        $this->builder->shouldReceive('where')->twice()->andReturn($this->builder);

        $this->builder->shouldReceive('update')->once()->andReturn(true);

        $result = $this->accessTokenRepository->update(1, 'token', Carbon::create(2016, 12, 29, 12));

        $this->assertTrue($result);
    }

    /** @test */
    public function it_shall_store_new_access_tokens_with_explicit_expire_dates()
    {
        $this->connection->shouldReceive('table')->once()->andReturn($this->builder);

        Carbon::setTestNow(Carbon::create(2016, 12, 28, 23));

        $this->builder->shouldReceive('insertGetId')->once()->with([
            'authenticatable_id' => 1,
            'token'              => 'foo',
            'created_at'         => '2016-12-28 23:00:00',
            'expires_at'         => '2016-12-28 23:15:00',
        ])->andReturn((object) []);

        $this->accessTokenRepository->store(1, 'foo', (string) Carbon::now()->addMinutes(15));
    }

    /** @test */
    public function it_shall_return_null_if_the_access_token_is_not_found()
    {
        $this->connection->shouldReceive('table')->once()->andReturn($this->builder);

        $this->builder->shouldReceive('where')->once()->withArgs(['token', 'foo'])->andReturn($this->builder);

        $this->builder->shouldReceive('first')->once()->andReturn(null);

        $accessToken = $this->accessTokenRepository->retrieveByAttributes(['token' => 'foo']);

        $this->assertNull($accessToken);
    }

    /** @test */
    public function it_shall_return_null_if_the_access_token_is_is_expired()
    {
        $this->connection->shouldReceive('table')->once()->andReturn($this->builder);

        $this->builder->shouldReceive('where')->once()->withArgs(['token', 'foo'])->andReturn($this->builder);

        Carbon::setTestNow(Carbon::create(2016, 12, 28, 23));

        $this->builder->shouldReceive('first')->once()->andReturn((object) [
            'id'                 => 1,
            'authenticatable_id' => 1,
            'token'              => 'foo',
            'created_at'         => '2016-12-28 23:00:00',
            'expires_at'         => '2016-12-28 22:29:59',
        ]);

        $accessToken = $this->accessTokenRepository->retrieveByAttributes(['token' => 'foo']);

        $this->assertNull($accessToken);
    }

    /** @test */
    public function it_shall_retrieve_an_access_token_with_the_given_attributes()
    {
        $this->connection->shouldReceive('table')->once()->andReturn($this->builder);

        $this->builder->shouldReceive('where')->once()->withArgs(['token', 'foo'])->andReturn($this->builder);

        Carbon::setTestNow(Carbon::create(2016, 12, 28, 23));

        $this->builder->shouldReceive('first')->once()->andReturn((object) [
            'id'                 => 1,
            'authenticatable_id' => 1,
            'token'              => 'foo',
            'created_at'         => '2016-12-28 23:00:00',
            'expires_at'         => '2016-12-28 23:15:00',
        ]);

        $accessToken = $this->accessTokenRepository->retrieveByAttributes(['token' => 'foo']);

        $this->assertEquals($accessToken->id, 1);
    }

    /** @test */
    public function it_shall_delete_an_access_token()
    {
        $this->connection->shouldReceive('table')->once()->andReturn($this->builder);

        $this->builder->shouldReceive('where')->twice()->andReturn($this->builder);

        $this->builder->shouldReceive('delete')->once()->andReturn(1);

        $this->accessTokenRepository->delete(1, 'foo');
    }

    /** @test */
    public function it_shall_delete_expired_access_tokens()
    {
        $this->connection->shouldReceive('table')->once()->andReturn($this->builder);

        Carbon::setTestNow(Carbon::create(2016, 12, 28, 23));

        $this->builder->shouldReceive('where')->with('expires_at', '<=', '2016-12-28 23:00:00')->andReturn($this->builder);

        $this->builder->shouldReceive('delete')->once()->andReturn(true);

        $this->accessTokenRepository->deleteExpired();
    }
}
