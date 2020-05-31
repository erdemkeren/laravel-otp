<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp\Http\Middleware;

use Closure;
use Erdemkeren\Otp\OtpFacade;
use Erdemkeren\Otp\TokenInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
            $this->sendNewOtpToUser($user);

            return $this->redirectToOtpPage();
        }

        $token = OtpFacade::retrieveByCipherText(
            $user->getAuthIdentifier(),
            $request->cookie('otp_token')
        );

        if (! $token || $token->expired()) {
            $this->sendNewOtpToUser($user);

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

    /**
     * Create a new otp and notify the user.
     *
     * @param Authenticatable $user
     */
    private function sendNewOtpToUser(Authenticatable $user): void
    {
        $token = OtpFacade::create($user, 6);

        if (! method_exists($user, 'notify')) {
            throw new \UnexpectedValueException(
                'The otp owner should be an instance of notifiable or implement the notify method.'
            );
        }

        $user->notify($token->toNotification());
    }
}
