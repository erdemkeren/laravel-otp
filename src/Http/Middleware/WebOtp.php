<?php

/*
 * @copyright 2021 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp\Http\Middleware;

use Closure;
use Erdemkeren\Otp\OtpToken;
use Illuminate\Http\Request;
use Erdemkeren\Otp\OtpFacade;
use Illuminate\Http\RedirectResponse;
use Erdemkeren\Otp\Exceptions\AuthenticationException;

class WebOtp
{
    public function handle(Request $request, Closure $next, ?string $guard = null): mixed
    {
        if (!$user = $request->user($guard)) {
            throw new AuthenticationException();
        }

        if (!$cipher = $request->cookie('otp_token')) {
            OtpFacade::sendNewOtp($user);

            return $this->redirectToOtpPage();
        }

        $token = OtpFacade::retrieveByCipherText($cipher);

        if (!$token || $token->expired()) {
            OtpFacade::sendNewOtp($user);

            return $this->redirectToOtpPage();
        }

        $request->macro('otpToken', function () use ($token): OtpToken {
            return $token;
        });

        return $next($request);
    }

    protected function redirectToOtpPage(): RedirectResponse
    {
        session([
            'otp_requested'    => true,
            'otp_redirect_url' => url()->current(),
        ]);

        return redirect()->route('otp.create');
    }
}
