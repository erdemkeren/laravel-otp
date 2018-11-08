<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp\Http\Controllers;

use Mockery as M;
use Illuminate\Http\Request;
use Erdemkeren\Otp\OtpService;
use PHPUnit\Framework\TestCase;
use Erdemkeren\Otp\TokenInterface;
use Illuminate\Support\MessageBag;
use Illuminate\Container\Container;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Facade;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Validation\Validator;

if (! \function_exists('\Erdemkeren\Otp\Http\Controllers\session')) {
    function session($a = null, $b = null)
    {
        global $testerClass;

        return $testerClass::$functions->session($a, $b);
    }
}

if (! \function_exists('\Erdemkeren\Otp\Http\Controllers\cookie')) {
    function cookie()
    {
        global $testerClass;

        return $testerClass::$functions->cookie();
    }
}

if (! \function_exists('\Erdemkeren\Otp\Http\Controllers\view')) {
    function view($a)
    {
        global $testerClass;

        return $testerClass::$functions->view($a);
    }
}

if (! \function_exists('\Erdemkeren\Otp\Http\Controllers\redirect')) {
    function redirect($a = null)
    {
        global $testerClass;

        return $testerClass::$functions->redirect($a);
    }
}

/**
 * @covers \Erdemkeren\Otp\Http\Controllers\OtpController
 */
class OtpControllerTest extends TestCase
{
    public static $functions;

    private $validator;

    private $service;

    public function setUp()
    {
        global $testerClass;
        $testerClass = self::class;
        $this::$functions = M::mock();

        $this->validator = M::mock(Validator::class);
        $this->service = M::mock(OtpService::class);

        $app = new Container();
        $app->singleton('app', 'Illuminate\Container\Container');
        // $app->singleton('config', 'Illuminate\Config\Repository');

        $app->bind('validator', function ($app) {
            return $this->validator;
        });

        $app->singleton('otp', function ($app) {
            return $this->service;
        });

        Facade::setFacadeApplication($app);
    }

    public function tearDown()
    {
        M::close();

        global $testerClass;
        $testerClass = null;

        Facade::clearResolvedInstances();

        parent::tearDown();
    }

    public function testCreate()
    {
        $controller = new OtpController();

        $this::$functions->shouldReceive('session')
            ->once()->with('otp_requested', false)
            ->andReturn(true);

        $this::$functions->shouldReceive('view')
            ->once()->with('otp.create')
            ->andReturn('view');

        $this->assertSame('view', $controller->create());
    }

    public function testCreateRedirectsWhenNotRedirectedByMiddleware()
    {
        $controller = new OtpController();

        $request = M::mock(Request::class);

        $this::$functions->shouldReceive('session')
            ->once()->with('otp_requested', false)
            ->andReturn(false);

        $this::$functions->shouldReceive('redirect')
            ->once()
            ->andReturn($response = M::mock(RedirectResponse::class));

        $this->assertSame($response, $controller->create($request));
    }

    public function testStore()
    {
        $controller = new OtpController();

        $request = M::mock(Request::class);

        $this::$functions->shouldReceive('session')
            ->once()->with('otp_requested', false)
            ->andReturn(true);

        $request->shouldReceive('all')
            ->once()
            ->andReturn([
                'password' => $password = '12345',
            ]);

        $this->validator->shouldReceive('make')
            ->once()->with([
                'password' => $password,
            ], [
                'password' => 'required|string',
            ])->andReturn($this->validator);

        $this->validator->shouldReceive('fails')
            ->once()
            ->andReturn(false);

        $request->shouldReceive('user')
            ->once()
            ->andReturn($authenticable = M::mock(Authenticatable::class));

        $request->shouldReceive('input')
            ->once()->with('password')
            ->andReturn($password);

        $this->service->shouldReceive('retrieveByPlainText')
            ->once()->with($authenticable, $password)
            ->andReturn($token = M::mock(TokenInterface::class));

        $token->ShouldReceive('expired')
            ->once()
            ->andReturn(false);

        $this::$functions->shouldReceive('session')
            ->twice()->with(null, null)
            ->andReturn(new class(self::$functions) {
                private $funcs;

                public function __construct($funcs)
                {
                    $this->funcs = $funcs;
                }

                public function forget($arg)
                {
                    $this->funcs->forget($arg);
                }

                public function pull($arg)
                {
                    return $this->funcs->pull($arg);
                }
            });

        $this::$functions->shouldReceive('forget')
            ->once()->with('otp_requested')
            ->andReturn(true);

        $this::$functions->shouldReceive('redirect')
            ->once()
            ->andReturn($c = new class(self::$functions) {
                private $funcs;

                public function __construct($funcs)
                {
                    $this->funcs = $funcs;
                }

                public function to($arg)
                {
                    return $this->funcs->to($arg);
                }

                public function withCookie($arg)
                {
                    return $this->funcs->withCookie($arg);
                }
            });

        $this::$functions->shouldReceive('pull')
            ->once()
            ->with('otp_redirect_url')
            ->andReturn($otpRedirectUrl = 'url');

        $this::$functions->shouldReceive('to')
            ->once()
            ->with($otpRedirectUrl)
            ->andReturn($c);

        $token->ShouldReceive('expiryTime')
            ->once()
            ->andReturn(60);

        $this::$functions->shouldReceive('withCookie')
            ->once()
            ->with('foo')
            ->andReturn($response = M::mock(RedirectResponse::class));

        $token->shouldReceive('__toString')->once()->andReturn($password);

        $this::$functions->shouldReceive('make')
            ->once()
            ->with('otp_token', $password, 1)
            ->andReturn('foo');

        $this::$functions->shouldReceive('cookie')
            ->once()
            ->andReturn(new class($this::$functions) {
                private $funcs;

                public function __construct($funcs)
                {
                    $this->funcs = $funcs;
                }

                public function make($n, $v, $t)
                {
                    return $this->funcs->make($n, $v, $t);
                }
            });

        $this->assertSame($response, $controller->store($request));
    }

    public function testStoreShouldRedirectBackWithErrorsOnValidationError()
    {
        $controller = new OtpController();

        $request = M::mock(Request::class);

        $this::$functions->shouldReceive('session')
            ->once()->with('otp_requested', false)
            ->andReturn(true);

        $request->shouldReceive('all')
            ->once()
            ->andReturn([
                'password' => $password = '12345',
            ]);

        $this->validator->shouldReceive('make')
            ->once()->with([
                'password' => $password,
            ], [
                'password' => 'required|string',
            ])->andReturn($this->validator);

        $this->validator->shouldReceive('fails')
            ->once()
            ->andReturn(true);

        $this::$functions->shouldReceive('redirect')
            ->once()
            ->andReturn($c = new class(self::$functions) {
                private $funcs;

                public function __construct($funcs)
                {
                    $this->funcs = $funcs;
                }

                public function back()
                {
                    return $this->funcs->redirectBack();
                }

                public function withErrors($arg)
                {
                    return $this->funcs->redirectBackWithErrors($arg);
                }
            });

        $this::$functions->shouldReceive('redirectBack')
            ->once()
            ->andReturn($c);

        $this::$functions->shouldReceive('redirectBackWithErrors')
            ->once()
            ->andReturn($response = M::mock(RedirectResponse::class));

        $this->assertSame($response, $controller->store($request));
    }

    public function testStoreShouldRedirectBackWithErrorsOnInvalidPassword()
    {
        $controller = new OtpController();

        $request = M::mock(Request::class);

        $this::$functions->shouldReceive('session')
            ->once()->with('otp_requested', false)
            ->andReturn(true);

        $request->shouldReceive('all')
            ->once()
            ->andReturn([
                'password' => $password = '12345',
            ]);

        $this->validator->shouldReceive('make')
            ->once()->with([
                'password' => $password,
            ], [
                'password' => 'required|string',
            ])->andReturn($this->validator);

        $this->validator->shouldReceive('fails')
            ->once()
            ->andReturn(false);

        $request->shouldReceive('user')
            ->once()
            ->andReturn($authenticable = M::mock(Authenticatable::class));

        $request->shouldReceive('input')
            ->once()->with('password')
            ->andReturn($password);

        $this->service->shouldReceive('retrieveByPlainText')
            ->once()->with($authenticable, $password)
            ->andReturnNull();

        $this->validator->shouldReceive('getMessageBag')
            ->once()
            ->andReturn($msgBag = M::mock(MessageBag::class));

        $msgBag->shouldReceive('add')
            ->once()->with('password', 'The password is not valid.')
            ->andReturnNull();

        $this::$functions->shouldReceive('redirect')
            ->once()
            ->andReturn($c = new class(self::$functions) {
                private $funcs;

                public function __construct($funcs)
                {
                    $this->funcs = $funcs;
                }

                public function back()
                {
                    return $this->funcs->redirectBack();
                }

                public function withErrors($arg)
                {
                    return $this->funcs->redirectBackWithErrors($arg);
                }
            });

        $this::$functions->shouldReceive('redirectBack')
            ->once()
            ->andReturn($c);

        $this::$functions->shouldReceive('redirectBackWithErrors')
            ->once()
            ->andReturn($response = M::mock(RedirectResponse::class));

        $this->assertSame($response, $controller->store($request));
    }

    public function testStoreShouldRedirectBackWithErrorsOnExpiredToken()
    {
        $controller = new OtpController();

        $request = M::mock(Request::class);

        $this::$functions->shouldReceive('session')
            ->once()->with('otp_requested', false)
            ->andReturn(true);

        $request->shouldReceive('all')
            ->once()
            ->andReturn([
                'password' => $password = '12345',
            ]);

        $this->validator->shouldReceive('make')
            ->once()->with([
                'password' => $password,
            ], [
                'password' => 'required|string',
            ])->andReturn($this->validator);

        $this->validator->shouldReceive('fails')
            ->once()
            ->andReturn(false);

        $request->shouldReceive('user')
            ->once()
            ->andReturn($authenticable = M::mock(Authenticatable::class));

        $request->shouldReceive('input')
            ->once()->with('password')
            ->andReturn($password);

        $this->service->shouldReceive('retrieveByPlainText')
            ->once()->with($authenticable, $password)
            ->andReturn($token = M::mock(TokenInterface::class));

        $token->shouldReceive('expired')
            ->once()
            ->andReturn(true);

        $this->validator->shouldReceive('getMessageBag')
            ->once()
            ->andReturn($msgBag = M::mock(MessageBag::class));

        $msgBag->shouldReceive('add')
            ->once()->with('password', 'The password is expired.')
            ->andReturnNull();

        $this::$functions->shouldReceive('redirect')
            ->once()
            ->andReturn($c = new class(self::$functions) {
                private $funcs;

                public function __construct($funcs)
                {
                    $this->funcs = $funcs;
                }

                public function back()
                {
                    return $this->funcs->redirectBack();
                }

                public function withErrors($arg)
                {
                    return $this->funcs->redirectBackWithErrors($arg);
                }
            });

        $this::$functions->shouldReceive('redirectBack')
            ->once()
            ->andReturn($c);

        $this::$functions->shouldReceive('redirectBackWithErrors')
            ->once()
            ->andReturn($response = M::mock(RedirectResponse::class));

        $this->assertSame($response, $controller->store($request));
    }

    public function testStoreRedirectsWhenNotRedirectedByMiddleware()
    {
        $controller = new OtpController();

        $request = M::mock(Request::class);

        $this::$functions->shouldReceive('session')
            ->once()->with('otp_requested', false)
            ->andReturn(false);

        $this::$functions->shouldReceive('redirect')
            ->once()
            ->andReturn($response = M::mock(RedirectResponse::class));

        $this->assertSame($response, $controller->store($request));
    }
}
