<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp;

use Mockery as M;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\DB;

if (! \function_exists('\Erdemkeren\Otp\config')) {
    function config($key)
    {
        global $testerClass;

        return $testerClass::$functions->config($key);
    }
}

/** @covers \Erdemkeren\Otp\Token */
class TokenTest extends TestCase
{
    public static $functions;

    /**
     * @var Token
     */
    private $token;

    public function setUp(): void
    {
        Carbon::setTestNow(new Carbon('2018-11-06 00:00:00'));

        $this->token = new Token(
            1,
            'foo',
            'bar',
            10,
            null,
            Carbon::now(),
            Carbon::now()
        );

        static::$functions = M::mock();
        global $testerClass;

        $testerClass = self::class;
    }

    public function tearDown()
    {
        M::close();
    }

    public function testRefresh()
    {
        Carbon::setTestNow(new Carbon('2018-11-06 00:00:01'));

        $this->persistShouldBeCalled();

        $this->token->refresh();

        $this->assertSame(11, $this->token->expiryTime());
    }

    public function testExpiresAt()
    {
        $this->assertSame('2018-11-06 00:00:10', $this->token->expiresAt()->toDateTimeString());
    }

    public function testItDoesNotConstructWithNullAuthenticableId()
    {
        $this->expectException(\LogicException::class);

        new Token(
            null,
            'foo',
            'bar',
            10,
            null,
            Carbon::now(),
            Carbon::now()
        );
    }

    public function testAuthenticableId()
    {
        $this->assertSame(1, $this->token->authenticableId());
    }

    public function testExpiryTime()
    {
        $this->assertSame(10, $this->token->expiryTime());
    }

    public function testPlainText()
    {
        $this->assertSame('bar', $this->token->plainText());
    }

    public function testScope()
    {
        $this->assertSame('default', $this->token->scope());
    }

    public function testScopeWithCustomValue()
    {
        $scopedToken = new Token(
            1,
            'foo',
            'bar',
            10,
            'custom',
            Carbon::now(),
            Carbon::now()
        );

        $this->assertSame('custom', $scopedToken->scope());
    }

    public function testCreate()
    {
        Carbon::setTestNow(new Carbon('2018-11-06 00:00:00'));

        $this::$functions->shouldReceive('config')
            ->once()->with('otp.table')
            ->andReturn($tableName = 'foes');

        $this::$functions->shouldReceive('config')
            ->once()->with('otp.expires')
            ->andReturn($expiryTimeMins = 10);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('table')->with($tableName)->once()->andReturnSelf();
        DB::shouldReceive('updateOrInsert')
            ->once()
            ->with([
                'authenticable_id' => $authenticableId = 1,
                'cipher_text'      => $cipherText = 'foo',
            ], [
                'authenticable_id' => $authenticableId,
                'expiry_time'      => $expiryTimeMins * 60,
                'cipher_text'      => $cipherText,
                'created_at'       => '2018-11-06 00:00:00',
                'updated_at'       => '2018-11-06 00:00:00',
            ])
            ->andReturn(true);
        DB::shouldReceive('commit')->once();

        $newToken = $this->token::create(
            1,
            'foo',
            'bar'
        );

        $this->assertInstanceOf(TokenInterface::class, $newToken);
    }

    public function testCreateWithForcedDefaultScope()
    {
        Carbon::setTestNow(
            new Carbon('2018-11-06 00:00:00')
        );

        $this::$functions->shouldReceive('config')
            ->once()->with('otp.table')
            ->andReturn($tableName = 'foes');

        $this::$functions->shouldReceive('config')
            ->once()->with('otp.expires')
            ->andReturn($expiryTimeMins = 10);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('table')->with($tableName)->once()->andReturnSelf();
        DB::shouldReceive('updateOrInsert')
            ->once()
            ->with([
                'authenticable_id' => $authenticableId = 1,
                'cipher_text'      => $cipherText = 'foo',
                'scope'            => null,
            ], [
                'authenticable_id' => $authenticableId,
                'cipher_text'      => $cipherText,
                'expiry_time'      => $expiryTimeMins * 60,
                'scope'            => null,
                'created_at'       => '2018-11-06 00:00:00',
                'updated_at'       => '2018-11-06 00:00:00',
            ])
            ->andReturn(true);
        DB::shouldReceive('commit')->once();

        $defaultScopedToken = Token::create(
            1,
            'foo',
            'bar',
            null,
            'default',
            Carbon::now(),
            Carbon::now()
        );

        $this->assertInstanceOf(TokenInterface::class, $defaultScopedToken);
    }

    public function testCreateWithCustomScope()
    {
        Carbon::setTestNow(
            new Carbon('2018-11-06 00:00:00')
        );

        $this::$functions->shouldReceive('config')
            ->once()->with('otp.table')
            ->andReturn($tableName = 'foes');

        $this::$functions->shouldReceive('config')
            ->once()->with('otp.expires')
            ->andReturn($expiryTimeMins = 10);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('table')->with($tableName)->once()->andReturnSelf();
        DB::shouldReceive('updateOrInsert')
            ->once()
            ->with([
                'authenticable_id' => $authenticableId = 1,
                'cipher_text'      => $cipherText = 'foo',
                'scope'            => 'custom',
            ], [
                'authenticable_id' => $authenticableId,
                'cipher_text'      => $cipherText,
                'expiry_time'      => $expiryTimeMins * 60,
                'scope'            => 'custom',
                'created_at'       => '2018-11-06 00:00:00',
                'updated_at'       => '2018-11-06 00:00:00',
            ])
            ->andReturn(true);
        DB::shouldReceive('commit')->once();

        $defaultScopedToken = Token::create(
            1,
            'foo',
            'bar',
            null,
            'custom',
            Carbon::now(),
            Carbon::now()
        );

        $this->assertInstanceOf(TokenInterface::class, $defaultScopedToken);
    }

    public function testPersistenceShouldHandleErrors()
    {
        $this->expectException(\RuntimeException::class);

        Carbon::setTestNow(new Carbon('2018-11-06 00:00:00'));

        $this::$functions->shouldReceive('config')
            ->once()->with('otp.table')
            ->andReturn($tableName = 'foes');

        $this::$functions->shouldReceive('config')
            ->once()->with('otp.expires')
            ->andReturn($expiryTimeMins = 10);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('table')->with($tableName)->once()->andReturnSelf();
        DB::shouldReceive('updateOrInsert')
            ->once()
            ->with([
                'authenticable_id' => $authenticableId = 2,
                'cipher_text'      => $cipherText = 'zaz',
                'scope'            => null,
            ], [
                'authenticable_id' => $authenticableId,
                'cipher_text'      => $cipherText,
                'expiry_time'      => $expiryTimeMins * 60,
                'scope'            => null,
                'created_at'       => '2018-11-06 00:00:00',
                'updated_at'       => '2018-11-06 00:00:00',
            ])
            ->andThrow(\RuntimeException::class);

        DB::shouldReceive('rollBack')->once();

        $newToken = $this->token::create(
            $authenticableId,
            $cipherText
        );

        $this->assertInstanceOf(TokenInterface::class, $newToken);
    }

    public function testToNotification()
    {
        $this->assertInstanceOf(TokenNotification::class, $this->token->toNotification());
        $this->assertSame($this->token, $this->token->toNotification()->token);
    }

    public function testCreatedAt()
    {
        $this->assertSame('2018-11-06 00:00:00', $this->token->createdAt()->toDateTimeString());
    }

    public function testUpdatedAt()
    {
        $this->assertSame('2018-11-06 00:00:00', $this->token->updatedAt()->toDateTimeString());
    }

    public function testExpired()
    {
        Carbon::setTestNow(new Carbon('2018-11-06 00:00:11'));

        $this->assertTrue($this->token->expired());

        Carbon::setTestNow(new Carbon('2018-11-06 00:00:05'));

        $this->assertFalse($this->token->expired());
    }

    public function testExtend()
    {
        $this->persistShouldBeCalled();

        $this->token->extend(1);

        $this->assertSame(11, $this->token->expiryTime());
    }

    public function testItCastsToString()
    {
        $this->assertSame('foo', (string) $this->token);
    }

    public function testRetrieveByAttributesCanReturnEmptyResults()
    {
        $this::$functions->shouldReceive('config')
            ->once()->with('otp.table')
            ->andReturn($tableName = 'foes');

        DB::shouldReceive('table')->with($tableName)->once()->andReturnSelf();
        DB::shouldReceive('first')->once()->andReturnNull();

        $result = $this->token->retrieveByAttributes([]);
        $this->assertNull($result);
    }

    public function testRetrieveByAttributes()
    {
        $this::$functions->shouldReceive('config')
            ->once()->with('otp.table')
            ->andReturn($tableName = 'foes');

        DB::shouldReceive('table')->with($tableName)->once()->andReturnSelf();
        DB::shouldReceive('where')->once()->with('foo', 'bar')->andReturnSelf();
        DB::shouldReceive('where')->once()->with('baz', 'qux')->andReturnSelf();
        DB::shouldReceive('first')->once()->andReturn((object) [
            'authenticable_id' => 'foo',
            'cipher_text'      => 'bar',
            'expiry_time'      => 10,
            'created_at'       => '2018-11-06 00:00:00',
            'updated_at'       => '2018-11-06 00:00:00',
        ]);

        $result = $this->token->retrieveByAttributes(['foo' => 'bar', 'baz' => 'qux']);
        $this->assertInstanceOf(TokenInterface::class, $result);
    }

    public function testCipherText()
    {
        $this->assertSame('foo', (string) $this->token);
    }

    public function testTimeLeft()
    {
        Carbon::setTestNow(new Carbon('2018-11-06 00:00:05'));

        $this->assertSame(5, $this->token->timeLeft());
    }

    public function testRevoke()
    {
        $this->persistShouldBeCalled();

        $this->token->revoke();

        $this->assertSame(0, $this->token->expiryTime());
    }

    public function testInvalidate()
    {
        $this->persistShouldBeCalled();

        $this->token->invalidate();

        $this->assertSame(0, $this->token->expiryTime());
    }

    private function persistShouldBeCalled(): void
    {
        $this::$functions->shouldReceive('config')
            ->once()->with('otp.table')
            ->andReturn($tableName = 'foes');

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('table')->with($tableName)->once()->andReturnSelf();
        DB::shouldReceive('updateOrInsert')->once()->andReturn(true);
        DB::shouldReceive('commit')->once();
    }
}
