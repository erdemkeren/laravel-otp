<?php

namespace Erdemkeren\TemporaryAccess\Middleware;

use Closure;
use Erdemkeren\TemporaryAccess\Token;
use Illuminate\Http\RedirectResponse;
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
        if(! $cipherText = $request->input('otp-token')) {
            return $this->redirectToOtpPage();
        }

        $authenticableId = auth($guard)->user()->getAuthIdentifier();
        if(! $token = TemporaryAccess::retrieveByCipherText($authenticableId, $cipherText)) {
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
}
