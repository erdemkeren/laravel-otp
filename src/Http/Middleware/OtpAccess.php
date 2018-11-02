<?php

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
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (! $user = $this->getAuthUser($guard)) {
            throw new \LogicException(
                "The otp access control middleware requires user authentication via laravel guards."
            );
        }

        if (! $cipherText = $request->input('otp-token')) {
            return $this->redirectToOtpPage();
        }

        /** @var Token $token */
        $token = TemporaryAccess::retrieveByCipherText($user->getAuthIdentifier(), $cipherText);
        if (! $token || $token->expired()) {
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
        return redirect()->to('otp/create');
    }

    /**
     * Get the guard by the given name.
     *
     * @param  string $guard
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
     * @param  string $guard
     *
     * @return Authenticatable|null
     */
    private function getAuthUser($guard): ?Authenticatable
    {
        return $this->getGuard($guard)->user();
    }
}
