<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\TemporaryAccess\PasswordGenerators;

use Erdemkeren\TemporaryAccess\PasswordGeneratorInterface;

/**
 * Class StringPasswordGenerator.
 */
class StringPasswordGenerator implements PasswordGeneratorInterface
{
    /**
     * Generate a string password with the given length.
     *
     * @param int $length
     *
     * @return string
     */
    public function generate(int $length): string
    {
        return str_random($length);
    }
}
