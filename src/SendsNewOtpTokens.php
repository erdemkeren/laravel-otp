<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Trait SendsNewOtpTokens.
 */
trait SendsNewOtpTokens
{
    /**
     * Create a new otp and notify the user.
     *
     * @param Authenticatable|Notifiable $user
     * @param null|string                $scope
     * @param null|int                   $length
     * @param null|int                   $expiryTime
     * @param null|string                $generator
     */
    protected function sendNewOtpTokenToUser(
        Authenticatable $user,
        ?string $scope = null,
        ?int $length = null,
        ?int $expiryTime = null,
        ?string $generator = null
    ): void {
        $token = OtpFacade::create($user, $scope, $length, $expiryTime, $generator);

        if (! method_exists($user, 'notify')) {
            throw new \UnexpectedValueException(
                'The otp owner should be an instance of notifiable or implement the notify method.'
            );
        }

        $user->notify($token->toNotification());
    }
}
