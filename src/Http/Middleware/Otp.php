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
use Illuminate\Contracts\Auth\Authenticatable;

class Otp
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param null|string              $scope
     * @param array                    ...$args
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ?string $scope = null, array ...$args)
    {
        if (! $user = $request->user()) {
            throw new \LogicException(
                'The otp access control middleware requires user authentication via laravel guards.'
            );
        }

        $expires = null;
        $length = null;
        foreach ($args as $arg) {
            if (strpos($arg, 'secs')) {
                $secs = str_replace('secs', '', $arg);
                if (\is_int($secs)) {
                    $expires = $secs;
                }

                continue;
            }

            if (strpos($arg, 'chars')) {
                $chars = str_replace('chars', '', $arg);
                if (\is_int($chars)) {
                    $length = $chars;
                }
            }
        }

        if (! $request->hasCookie('otp_token')) {
            $this->sendNewOtpToUser($user, $scope, $length, $expires);

            return $this->redirectToOtpPage($scope);
        }

        $token = OtpFacade::retrieveByCipherText(
            $user->getAuthIdentifier(),
            $request->cookie($scope ? $scope.'_' : 'otp_token'),
            $scope
        );

        if (! $token || $token->expired()) {
            $this->sendNewOtpToUser($user, $scope, $length, $expires);

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

    /**
     * Create a new otp and notify the user.
     *
     * @param Authenticatable $user
     * @param null|string     $scope
     * @param null|int        $length
     * @param null|int        $expires
     */
    private function sendNewOtpToUser(
        Authenticatable $user,
        ?string $scope = null,
        ?int $length = null,
        ?int $expires = null): void
    {
        $token = OtpFacade::create($user, $scope, $length, $expires);

        if (! method_exists($user, 'notify')) {
            throw new \UnexpectedValueException(
                'The otp owner should be an instance of notifiable or implement the notify method.'
            );
        }

        $user->notify($token->toNotification());
    }
}
