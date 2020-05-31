<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp\PasswordGenerators;

use Illuminate\Support\Str;
use Erdemkeren\Otp\PasswordGeneratorInterface;

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
        return Str::random($length);
    }
}
