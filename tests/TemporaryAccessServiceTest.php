<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\TemporaryAccess;

use Mockery as M;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Illuminate\Database\DatabaseManager;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Connectors\ConnectionFactory;

function config($key)
{
    return TemporaryAccessServiceTest::$functions->config($key);
}

/**
 * @coversNothing
 */
class TemporaryAccessServiceTest extends \PHPUnit\Framework\TestCase
{
    public static $functions = [];

    /**
     * @var int
     */
    private $pwdLength;

    /**
     * @var string
     */
    private $defaultGeneratorName;

    /**
     * @var TemporaryAccessService
     */
    private $service;

    /**
     * @var PasswordGeneratorManagerInterface
     */
    private $pwdGenManager;

    /**
     * @var Authenticatable
     */
    private $authenticable;

    /**
     * @var TokenInterface
     */
    private $token;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    public function setUp(): void
    {
        self::$functions = M::mock();

        $app = new Container();
        $app->singleton('app', 'Illuminate\Container\Container');
        $app->singleton('config', 'Illuminate\Config\Repository');

        $app->bind('db', function ($app) {
            return new DatabaseManager($app, new ConnectionFactory($app));
        });

        Facade::setFacadeApplication($app);

        $this->pwdGenManager = M::mock(PasswordGeneratorManagerInterface::class);
        $this->encryptor = M::mock(EncryptorInterface::class);
        $this->defaultGeneratorName = 'foo';
        $this->pwdLength = 6;

        $this->authenticable = M::mock(Authenticatable::class);
        $this->token = M::mock(TokenInterface::class);

        $this->service = new TemporaryAccessService(
            $this->pwdGenManager,
            $this->encryptor,
            $this->defaultGeneratorName,
            $this->pwdLength
        );
    }

    public function tearDown(): void
    {
        M::close();

        parent::tearDown();
    }

    public function testItCreatesTokens(): void
    {
        $this->pwdGenManager->shouldReceive('get')
            ->once()->with($this->defaultGeneratorName)
            ->andReturn($this->fooGenerator());

        $this->encryptor->shouldReceive('encrypt')
            ->once()->with('foo')
            ->andReturn('bar');

        $this->authenticable->shouldReceive('getAuthIdentifier')
            ->once()
            ->andReturn(1);

        $this::$functions->shouldReceive('config')
            ->once()->with('temporary_access.table')
            ->andReturn($tableName = 'foes');

        $this::$functions->shouldReceive('config')
            ->once()->with('temporary_access.expires')
            ->andReturn('900');

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('table')->with($tableName)->once()->andReturnSelf();
        DB::shouldReceive('updateOrInsert')->once()->andReturn(true);
        DB::shouldReceive('commit')->once();

        $token = $this->service->create($this->authenticable);

        $this->assertInstanceOf(TokenInterface::class, $token);
        $this->assertSame('foo', $token->plainText());
        $this->assertSame('bar', $token->cipherText());
    }

    public function testCheck(): void
    {
        $this->authenticable->shouldReceive('getAuthIdentifier')
            ->once()
            ->andReturn('1');

        $this::$functions->shouldReceive('config')
            ->once()->with('temporary_access.table')
            ->andReturn($tableName = 'foes');

        DB::shouldReceive('table')->once()->with($tableName)->andReturnSelf();
        DB::shouldReceive('where')->twice()->andReturnSelf();
        DB::shouldReceive('first')->once()->andReturn((object) [
            'authenticable_id' => 1,
            'cipher_text'      => $cipherText = 'foo',
            'created_at'       => '2018-11-06 14:44:00',
            'updated_at'       => '2018-11-06 14:44:00',
            'expiry_time'      => 10,
        ]);

        Carbon::setTestNow('2018-11-06 14:44:09');

        $result = $this->service->check($this->authenticable, $cipherText);
        $this->assertTrue($result);
    }

    public function testRetrieveByPlainText(): void
    {
        $this->authenticable->shouldReceive('getAuthIdentifier')
            ->once()
            ->andReturn($authenticableId = 1);

        $this->encryptor->shouldReceive('encrypt')
            ->once()->with($plainText = 'foo')
            ->andReturn('bar');

        $this::$functions->shouldReceive('config')
            ->once()->with('temporary_access.table')
            ->andReturn($tableName = 'foes');

        DB::shouldReceive('table')->once()->with($tableName)->andReturnSelf();
        DB::shouldReceive('where')->once()->with('authenticable_id', $authenticableId)->andReturnSelf();
        DB::shouldReceive('where')->once()->with('plain_text', $plainText)->andReturnSelf();
        DB::shouldReceive('first')->once()->andReturn((object) [
            'authenticable_id' => 1,
            'cipher_text'      => $token = 'bar',
            'created_at'       => '2018-11-06 14:44:00',
            'updated_at'       => '2018-11-06 14:44:00',
            'expiry_time'      => 10,
        ]);

        $token = $this->service->retrieveByPlainText($this->authenticable, $plainText);
        $this->assertInstanceOf(TokenInterface::class, $token);
    }

    public function testAddPasswordGenerator(): void
    {
        $this->pwdGenManager->shouldReceive('register')
            ->once()->with('acme', $generator = 'acme_generator')
            ->andReturnNull();

        $this->service->addPasswordGenerator('acme', 'acme_generator');
    }

    public function testSetPasswordGenerator(): void
    {
        $generatorResult = 'acme_generator_result';

        $this->pwdGenManager->shouldReceive('get')
            ->once()->with('acme')
            ->andReturn(function () use ($generatorResult) {
                return $generatorResult;
            });

        $this->encryptor->shouldReceive('encrypt')
            ->once()->with($generatorResult)
            ->andReturn('bar');

        $this->service->setPasswordGenerator('acme');

        $this->authenticable->shouldReceive('getAuthIdentifier')->once()->andReturn($authId = 1);

        $this::$functions->shouldReceive('config')
            ->once()->with('temporary_access.expires')
            ->andReturn(900);

        $this::$functions->shouldReceive('config')
            ->once()->with('temporary_access.table')
            ->andReturn($tableName = 'foes');

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('table')->with($tableName)->once()->andReturnSelf();
        DB::shouldReceive('updateOrInsert')->once()->andReturn(true);
        DB::shouldReceive('commit')->once();

        $token = $this->service->create($this->authenticable);
        $this->assertSame($generatorResult, $token->plainText());
    }

    public function testRetrieveByCipherText(): void
    {
        $this->authenticable->shouldReceive('getAuthIdentifier')
            ->once()
            ->andReturn($authenticableId = 1);

        $this::$functions->shouldReceive('config')
            ->once()->with('temporary_access.table')
            ->andReturn($tableName = 'foes');

        DB::shouldReceive('table')->once()->with($tableName)->andReturnSelf();
        DB::shouldReceive('where')->once()->with('authenticable_id', $authenticableId)->andReturnSelf();
        DB::shouldReceive('where')->once()->with('cipher_text', $cipherText = 'bar')->andReturnSelf();
        DB::shouldReceive('first')->once()->andReturn((object) [
            'authenticable_id' => 1,
            'cipher_text'      => $cipherText,
            'created_at'       => '2018-11-06 14:44:00',
            'updated_at'       => '2018-11-06 14:44:00',
            'expiry_time'      => 10,
        ]);

        $token = $this->service->retrieveByCipherText($this->authenticable, $cipherText);
        $this->assertInstanceOf(TokenInterface::class, $token);
    }

    private function fooGenerator(): callable
    {
        return function (): string {
            return 'foo';
        };
    }
}
