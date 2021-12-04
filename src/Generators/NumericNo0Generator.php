<?php

/*
 * @copyright 2021 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp\Generators;

use Throwable;
use Erdemkeren\Otp\Contracts\GeneratorContract;

class NumericNo0Generator extends NumericGenerator implements GeneratorContract
{
    public function generate(int $length = null): string
    {
        return (string) str_replace(
            0,
            $this->getRandomDigitWithNo0(),
            parent::generate($length)
        );
    }

    private function getRandomDigitWithNo0(): int
    {
        try {
            $int = random_int(1, 9);
        } catch (Throwable) {
            $int = rand(1, 9);
        }

        return $int;
    }
}
