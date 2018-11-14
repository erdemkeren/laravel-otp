<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp;

use Illuminate\Support\Facades\Facade;

/**
 * Class OtpFacade.
 *
 * @method static check($authenticableId, string $token, ?string $scope = null): bool
 * @method static create($authenticatableId, ?string $scope = null, ?int $length = null, ?int $expires = null): TokenInterface
 * @method static retrieveByPlainText($authenticableId, string $plainText, ?string $scope = null): ?TokenInterface
 * @method static retrieveByCipherText($authenticableId, string $cipherText, ?string $scope = null): ?TokenInterface
 * @method static addPasswordGenerator(string $name, $generator): void
 * @method static setPasswordGenerator(string $name): void
 */
class OtpFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'otp';
    }
}
