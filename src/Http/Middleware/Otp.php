<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Erdemkeren\Otp\OtpFacade;
use Erdemkeren\Otp\TokenInterface;
use Illuminate\Http\RedirectResponse;

class Otp
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param null|string              $guard
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        if (! $user = $request->user($guard)) {
            throw new \LogicException(
                'The otp access control middleware requires user authentication via laravel guards.'
            );
        }

        if (! $request->hasCookie('otp_token')) {
            OtpFacade::sendNewOtpToUser($user);

            return $this->redirectToOtpPage();
        }

        $token = OtpFacade::retrieveByCipherText(
            $user->getAuthIdentifier(),
            $request->cookie('otp_token')
        );

        if (! $token || $token->expired()) {
            OtpFacade::sendNewOtpToUser($user);

            return $this->redirectToOtpPage();
        }

        $request->macro('otpToken', function () use ($token): TokenInterface {
            return $token;
        });

        return $next($request);
    }

    /**
     * Get the redirect url if check do not pass.
     *
     * @return RedirectResponse
     */
    protected function redirectToOtpPage(): RedirectResponse
    {
        session([
            'otp_requested'    => true,
            'otp_redirect_url' => url()->current(),
        ]);

        return redirect()->route('otp.create');
    }
}
