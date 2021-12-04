<?php

/*
 * @copyright 2021 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp\Generators;

use Erdemkeren\Otp\Contracts\GeneratorContract;
use Illuminate\Support\Str;

class StringGenerator implements GeneratorContract
{
    public function generate(): string
    {
        return Str::random(8);
    }
}
