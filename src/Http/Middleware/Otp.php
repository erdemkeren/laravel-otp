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

        // Generate the scoped token cookie name.
        $cookieName = ($scope ?: TokenInterface::SCOPE_DEFAULT).'_otp_token';

        // Check token. If there is no token; send a token to user
        // and redirect the client browser to the otp page.
        if (! $request->hasCookie($cookieName)) {
            list($length, $expiryTime) = $this->getOtpOptions($args);

            $this->sendNewOtpTokenToUser($user, $scope, $length, $expiryTime);

            return $this->redirectToOtpPage($scope);
        }

        // Try to retrieve the otp token.
        $token = OtpFacade::retrieveByCipherText(
            $user->getAuthIdentifier(),
            $request->cookie($cookieName),
            $scope
        );

        // If there is no token, or the token is expired,
        // redirect the client browser to the otp page.
        if (! $token || $token->expired()) {
            list($length, $expiryTime) = $this->getOtpOptions($args);

            $this->sendNewOtpTokenToUser($user, $scope, $length, $expiryTime);

            return $this->redirectToOtpPage($scope);
        }

        // Add the otp token to request for further usage.
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

    /**
     * Get the options from the given arguments.
     *
     * @param $args
     *
     * @return array
     */
    private function getOtpOptions($args)
    {
        $options = [
            'length'     => null,
            'expiryTime' => null,
        ];

        foreach ($args as $arg) {
            $parts = explode('=', $arg);

            if (\in_array($parts[0], ['length', 'expiryTime'], true)) {
                $options[$parts[0]] = $parts[1];
            }
        }

        return array_values($options);
    }
}
