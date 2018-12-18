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
use Erdemkeren\Otp\SendsNewOtpTokens;
use Illuminate\Http\RedirectResponse;

class Otp
{
    use SendsNewOtpTokens;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param null|string              $scope
     * @param string                   ...$args
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ?string $scope = null, ...$args)
    {
        if (! $user = $request->user()) {
            throw new \LogicException(
                'The otp access control middleware requires user authentication via laravel guards.'
            );
        }

        $length = null;
        $expires = null;
        foreach ($args as $arg) {
            $parts = explode('=', $arg);

            if (\in_array($parts[0], ['length', 'expires'], true)) {
                $varname = $parts[0];
                ${$varname} = $parts[1];
            }
        }

        $cookieName = ($scope ?: TokenInterface::SCOPE_DEFAULT).'_otp_token';

        if (! $request->hasCookie($cookieName)) {
            $this->sendNewOtpTokenToUser($user, $scope, $length, $expires);

            return $this->redirectToOtpPage($scope);
        }

        $token = OtpFacade::retrieveByCipherText(
            $user->getAuthIdentifier(),
            $request->cookie($cookieName),
            $scope
        );

        if (! $token || $token->expired()) {
            $this->sendNewOtpTokenToUser($user, $scope, $length, $expires);

            return $this->redirectToOtpPage($scope);
        }

        $request->macro('otpToken', function () use ($token): TokenInterface {
            return $token;
        });

        return $next($request);
    }

    /**
     * Get the redirect url if check do not pass.
     *
     * @param null|string $scope
     *
     * @return RedirectResponse
     */
    protected function redirectToOtpPage(?string $scope = null): RedirectResponse
    {
        session([
            'otp_scope'        => $scope,
            'otp_requested'    => true,
            'otp_redirect_url' => url()->current(),
        ]);

        return redirect()->route('otp.create');
    }
}
