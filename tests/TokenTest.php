<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\TemporaryAccess;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

if (! \function_exists('\Erdemkeren\TemporaryAccess\config')) {
    function config($key)
    {
        global $testerClass;

        return $testerClass::$functions->config($key);
    }
}

/** @covers \Erdemkeren\TemporaryAccess\Token */
class TokenTest extends TestCase
{
    public static $functions;

    /**
     * @var Token
     */
    private $token;

    public function setUp(): void
    {
        Carbon::setTestNow('2018-11-06 00:00:00');

        $this->token = new Token(
            1,
            'foo',
            'bar',
            10,
            Carbon::now(),
            Carbon::now()
        );

        static::$functions = M::mock();
        global $testerClass;

        $testerClass = self::class;
    }

    public function testRefresh()
    {
        Carbon::setTestNow('2018-11-06 00:00:01');

        $this->token->refresh();

        $this->assertSame(11, $this->token->expiryTime());
    }

    public function testExpiresAt()
    {
    }

    public function testAuthenticableId()
    {
    }

    public function testExpiryTime()
    {
    }

    public function testPlainText()
    {
    }

    public function testCreate()
    {
    }

    public function testToNotification()
    {
    }

    public function testCreatedAt()
    {
    }

    public function testUpdatedAt()
    {
    }

    public function testExpired()
    {
    }

    public function testExtend()
    {
    }

    public function testRevoke()
    {
    }

    public function test__construct()
    {
    }

    public function test__toString()
    {
    }

    public function testRetrieveByAttributes()
    {
    }

    public function testCipherText()
    {
    }

    public function testTimeLeft()
    {
    }

    public function testInvalidate()
    {
    }
}
