<?php

/*
 * @copyright 2021 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp\Generators;

use Throwable;
use Erdemkeren\Otp\Contracts\GeneratorContract;

/**
 * Class NumericNo0Generator.
 */
class NumericNo0Generator extends NumericGenerator implements GeneratorContract
{
    /**
     * Generate a numeric token with no zeroes.
     *
     * @return string
     */
    public function generate(): string
    {
        return (string) str_replace(
            0,
            $this->getRandomDigitWithNo0(),
            parent::generate()
        );
    }

    /**
     * Generate a random digit with no zeroes.
     *
     * @return int
     */
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
