<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\TemporaryAccess\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Erdemkeren\TemporaryAccess\TokenInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Erdemkeren\TemporaryAccess\TemporaryAccessFacade as TemporaryAccess;

class OtpAccess
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

        $token = TemporaryAccess::retrieveByCipherText(
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
        $token = TemporaryAccess::create($user, 6);

        if (! method_exists($user, 'notify')) {
            throw new \UnexpectedValueException(
                'The otp owner should be an instance of notifiable or implement the notify method.'
            );
        }

        $user->notify($token->toNotification());
    }
}
