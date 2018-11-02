<?php

namespace Erdemkeren\TemporaryAccess;

use Illuminate\Support\Facades\Facade;

/**
 * Class TemporaryAccessFacade
 *
 * @method static check($authenticableId, string $token): bool
 * @method static setPasswordGenerator(string $name): void
 * @method static create($authenticatableId, ?int $length = null): TokenInterface
 * @method static retrieveByPlainText($authenticableId, string $plainText): ?TokenInterface
 * @method static retrieveByCipherText($authenticableId, string $cipherText): ?TokenInterface
 * @method static addPasswordGenerator(string $name, $generator): void
 */
class TemporaryAccessFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'temporary-access'; }
}
