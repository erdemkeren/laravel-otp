<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\TemporaryAccess;

use Mockery as M;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Illuminate\Database\DatabaseManager;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Connectors\ConnectionFactory;

if (! \function_exists('\Erdemkeren\TemporaryAccess\config')) {
    function config($key)
    {
        global $testerClass;

        return $testerClass::$functions->config($key);
    }
}

/**
 * @covers \Erdemkeren\TemporaryAccess\TemporaryAccessService
 */
class TemporaryAccessServiceTest extends TestCase
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

        global $testerClass;
        $testerClass = self::class;

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
            $this->pwdLength,
            FakeToken::class
        );
    }

    public function tearDown(): void
    {
        M::close();

        global $testerClass;
        $testerClass = null;

        parent::tearDown();
    }

    public function testItThrowRuntimeExceptionIfTokenClassNotFound(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->service = new TemporaryAccessService(
            $this->pwdGenManager,
            $this->encryptor,
            $this->defaultGeneratorName,
            $this->pwdLength,
            AcmeToken::class
        );
    }

    public function testItThrowRuntimeExceptionIfTokenIsNotInstantiable(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->service = new TemporaryAccessService(
            $this->pwdGenManager,
            $this->encryptor,
            $this->defaultGeneratorName,
            $this->pwdLength,
            AbstractToken::class
        );
    }

    public function testItThrowTypeErrorIfTokenIsNotAnInstanceOfTokenInterface(): void
    {
        $this->expectException(\TypeError::class);

        $this->service = new TemporaryAccessService(
            $this->pwdGenManager,
            $this->encryptor,
            $this->defaultGeneratorName,
            $this->pwdLength,
            NotImplementingToken::class
        );
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
            ->andReturn($authenticableId = 1);

        $this::$functions->shouldReceive('retrieveByAttributes')
            ->once()
            ->with(['authenticable_id' => $authenticableId, 'cipher_text' => $cipherText = 'bar'])
            ->andReturn(new FakeToken(
                1,
                $cipherText,
                null,
                10,
                new Carbon('2018-11-06 14:44:00'),
                new Carbon('2018-11-06 14:44:00')
            ));

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
            ->andReturn($cipherText = 'bar');

        $this::$functions->shouldReceive('retrieveByAttributes')
            ->once()
            ->with(['authenticable_id' => $authenticableId, 'cipher_text' => $cipherText = 'bar'])
            ->andReturn(new FakeToken(
                1,
                $cipherText,
                null,
                10,
                new Carbon('2018-11-06 14:44:00'),
                new Carbon('2018-11-06 14:44:00')
            ));

        $token = $this->service->retrieveByPlainText($this->authenticable, $plainText);
        $this->assertInstanceOf(TokenInterface::class, $token);
    }

    public function testAddSetPasswordGenerator(): void
    {
        $generatorResult = 'acme_generator_result';

        $this->pwdGenManager->shouldReceive('register')
            ->once()->with('acme', $generator = function () use ($generatorResult) {
                return $generatorResult;
            })->andReturnNull();

        $this->pwdGenManager->shouldReceive('get')
            ->once()->with('acme')
            ->andReturn($generator);

        $this->service->addPasswordGenerator('acme', $generator);

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

        $this::$functions->shouldReceive('retrieveByAttributes')
            ->once()
            ->with(['authenticable_id' => $authenticableId, 'cipher_text' => $cipherText = 'bar'])
            ->andReturn(new FakeToken(
                1,
                $cipherText,
                null,
                10,
                new Carbon('2018-11-06 14:44:00'),
                new Carbon('2018-11-06 14:44:00')
            ));

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

class FakeToken extends Token implements TokenInterface
{
    public static function retrieveByAttributes(array $attributes): ?TokenInterface
    {
        return TemporaryAccessServiceTest::$functions->retrieveByAttributes($attributes);
    }

    protected function persist(): bool
    {
        return true;
    }
}

abstract class AbstractToken implements TokenInterface
{
}

class NotImplementingToken
{
}
