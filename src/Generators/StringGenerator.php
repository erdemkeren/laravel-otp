<?php

/*
 * @copyright 2021 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp\Generators;

use Illuminate\Support\Str;
use Erdemkeren\Otp\Contracts\GeneratorContract;

/**
 * Class StringGenerator.
 */
class StringGenerator implements GeneratorContract
{
    /**
     * Generate a string password with the given length.
     *
     * @return string
     */
    public function generate(): string
    {
        return Str::random(8);
    }
}
