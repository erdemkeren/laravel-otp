<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp\Http\Middleware;

use Erdemkeren\Otp\OtpService;
use Erdemkeren\Otp\TokenInterface;
use Erdemkeren\Otp\TokenNotification;
use Illuminate\Container\Container;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Mockery as M;
use PHPUnit\Framework\TestCase;

if (! \function_exists('\Erdemkeren\Otp\Http\Middleware\session')) {
    function session(array $args)
    {
        global $testerClass;

        return $testerClass::$functions->session($args);
    }
}

if (! \function_exists('\Erdemkeren\Otp\Http\Middleware\url')) {
    function url()
    {
        global $testerClass;

        return $testerClass::$functions->url();
    }
}

if (! \function_exists('\Erdemkeren\Otp\Http\Middleware\redirect')) {
    function redirect()
    {
        global $testerClass;

        return $testerClass::$functions->redirect();
    }
}

class NotifiableAuthenticable implements Authenticatable
{
    public function getAuthIdentifierName()
    {
    }

    public function getAuthIdentifier()
    {
    }

    public function getAuthPassword()
    {
    }

    public function getRememberToken()
    {
    }

    public function setRememberToken($value)
    {
    }

    public function getRememberTokenName()
    {
    }

    public function notify()
    {
    }
}

/** @covers \Erdemkeren\Otp\Http\Middleware\Otp */
class OtpTest extends TestCase
{
    public static $functions;

    private $service;

    private $token;

    private $authenticable;

    private $tokenNotification;

    public function setUp()
    {
        global $testerClass;
        $testerClass = self::class;

        $app = new Container();
        $app->singleton('app', 'Illuminate\Container\Container');
        $app->singleton('config', 'Illuminate\Config\Repository');

        $this->token = M::mock(TokenInterface::class);
        $this->authenticable = M::mock(NotifiableAuthenticable::class);
        $this->service = M::mock(OtpService::class);
        $this->tokenNotification = M::mock(TokenNotification::class);

        $app->singleton('otp', function () {
            return $this->service;
        });

        Facade::setFacadeApplication($app);

        $this::$functions = M::mock();
    }

    public function tearDown()
    {
        M::close();

        global $testerClass;
        $testerClass = null;

        Facade::clearResolvedInstances();

        parent::tearDown();
    }

    public function testRequestIsPassedAlongIfTokenIsValid(): void
    {
        $middleware = new Otp();
        $request = Request::create('/');
        $request->cookies->set('otp_token', $token = 'token');
        $request->setUserResolver(function () {
            return $this->authenticable;
        });

        $this->service->shouldReceive('retrieveByCipherText')
            ->once()->with($authId = 1, $token)
            ->andReturn($this->token);

        $this->authenticable->shouldReceive('getAuthIdentifier')
            ->once()
            ->andReturn($authId);

        $this->token->shouldReceive('expired')
            ->once()
            ->andReturn(false);

        $response = $middleware->handle($request, function () {
            return 'response';
        });

        $this->assertInstanceOf(TokenInterface::class, $request->otpToken());

        $this->assertSame('response', $response);
    }

    public function testItThrowsLogicExceptionIfThereIsNoUserAuthenticated(): void
    {
        $this->expectException(\LogicException::class);

        $middleware = new Otp();
        $request = Request::create('/');

        $middleware->handle($request, function () {
        });
    }

    public function testRequestIsRedirectedToOtpCreateEndpointIfNoTokenExists(): void
    {
        $this->service->shouldReceive('create')
            ->once()
            ->andReturn($this->token);

        $this->token->shouldReceive('toNotification')
            ->once()
            ->andReturn($this->tokenNotification);

        $this->authenticable->shouldReceive('notify')
            ->once()->with($this->tokenNotification)
            ->andReturnNull();

        $this::$functions->shouldReceive('url')
            ->once()->andReturn(new class() {
                public function current(): string
                {
                    return 'foo';
                }
            });

        $this::$functions->shouldReceive('session')
            ->once()->with([
                'otp_requested'    => true,
                'otp_redirect_url' => 'foo',
            ])->andReturnNull();

        $this::$functions->shouldReceive('redirect')
            ->once()->andReturn(new class() {
                public function route()
                {
                    return M::mock(RedirectResponse::class);
                }
            });

        $middleware = new Otp();
        $request = Request::create('/');
        $request->setUserResolver(function () {
            return $this->authenticable;
        });

        $response = $middleware->handle($request, function () {
            return 'response';
        });

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testItThrowsUnexpectedValueExceptionIfTheAuthenticableIsNotNotifiable(): void
    {
        $this->service->shouldReceive('create')
            ->once()
            ->andReturn($this->token);

        $this->expectException(\UnexpectedValueException::class);

        $middleware = new Otp();
        $request = Request::create('/');
        $request->setUserResolver(function () {
            return M::mock(Authenticatable::class);
        });

        $response = $middleware->handle($request, function () {
            return 'response';
        });

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testRequestIsRedirectedToOtpCreateEndpointIfTokenIsExpired(): void
    {
        $middleware = new Otp();
        $request = Request::create('/');
        $request->cookies->set('otp_token', $token = 'token');
        $request->setUserResolver(function () {
            return $this->authenticable;
        });

        $this->service->shouldReceive('retrieveByCipherText')
            ->once()->with($authId = 1, $token)
            ->andReturn($this->token);

        $this->authenticable->shouldReceive('getAuthIdentifier')
            ->once()
            ->andReturn($authId);

        $this->token->shouldReceive('expired')
            ->once()
            ->andReturn(true);

        $this->service->shouldReceive('create')
            ->once()
            ->andReturn($this->token);

        $this->token->shouldReceive('toNotification')
            ->once()
            ->andReturn($this->tokenNotification);

        $this->authenticable->shouldReceive('notify')
            ->once()->with($this->tokenNotification)
            ->andReturnNull();

        $this::$functions->shouldReceive('url')
            ->once()->andReturn(new class() {
                public function current(): string
                {
                    return 'foo';
                }
            });

        $this::$functions->shouldReceive('session')
            ->once()->with([
                'otp_requested'    => true,
                'otp_redirect_url' => 'foo',
            ])->andReturnNull();

        $this::$functions->shouldReceive('redirect')
            ->once()->andReturn(new class() {
                public function route()
                {
                    return M::mock(RedirectResponse::class);
                }
            });

        $response = $middleware->handle($request, function () {
            return 'response';
        });

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }
}
