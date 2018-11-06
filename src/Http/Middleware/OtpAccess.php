<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\TemporaryAccess\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Erdemkeren\TemporaryAccess\Token;
use Illuminate\Http\RedirectResponse;
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
    public function handle($request, Closure $next, $guard = null)
    {
        if (! $user = $this->getAuthUser($guard)) {
            throw new \LogicException(
                'The otp access control middleware requires user authentication via laravel guards.'
            );
        }

        if (! $request->hasCookie('otp_token')) {
            $this->sendNewOtpToUser($request->user());

            return $this->redirectToOtpPage();
        }

        $token = TemporaryAccess::retrieveByCipherText(
            $user->getAuthIdentifier(),
            $request->cookie('otp_token')
        );

        if (! $token || $token->expired()) {
            $this->sendNewOtpToUser($request->user());

            return $this->redirectToOtpPage();
        }

        $request->macro('otpToken', function () use ($token): Token {
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
        return redirect()->route('otp.create');
    }

    /**
     * Get the guard by the given name.
     *
     * @param string $guard
     *
     * @return Guard
     */
    private function getGuard($guard): Guard
    {
        return auth()->guard($guard);
    }

    /**
     * Get the authenticated user from
     * the guard by the given name.
     *
     * @param string $guard
     *
     * @return null|Authenticatable
     */
    private function getAuthUser($guard): ?Authenticatable
    {
        return $this->getGuard($guard)->user();
    }

    /**
     * Create a new otp and notify the user.
     *
     * @param Authenticatable $user
     */
    private function sendNewOtpToUser(Authenticatable $user): void
    {
        $token = TemporaryAccess::create($user, 6);

        $user->notify($token->toNotification());
    }
}
