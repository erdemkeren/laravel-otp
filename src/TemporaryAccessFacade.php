<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\TemporaryAccess;

use Illuminate\Support\Facades\Facade;

/**
 * Class TemporaryAccessFacade.
 *
 * @method static setPasswordGenerator(string $name): void
 * @method static check($authenticableId, string $token): bool
 * @method static addPasswordGenerator(string $name, $generator): void
 * @method static create($authenticatableId, ?int $length = null): TokenInterface
 * @method static retrieveByPlainText($authenticableId, string $plainText): ?TokenInterface
 * @method static retrieveByCipherText($authenticableId, string $cipherText): ?TokenInterface
 */
class TemporaryAccessFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'temporary-access';
    }
}
