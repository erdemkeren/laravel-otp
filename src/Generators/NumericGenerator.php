<?php

/*
 * @copyright 2021 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp\Generators;

use Throwable;
use Erdemkeren\Otp\Contracts\GeneratorContract;

/**
 * Class NumericGenerator.
 */
class NumericGenerator implements GeneratorContract
{
    /**
     * Generate a numeric password.
     *
     * @return string
     */
    public function generate(): string
    {
        $range = $this->generateRangeForLength();

        try {
            $int = random_int($range[0], $range[1]);
        } catch (Throwable) {
            $int = rand($range[0], $range[1]);
        }

        return (string) $int;
    }

    /**
     * Generate the required range for the given length.
     *
     * @return array
     */
    protected function generateRangeForLength(): array
    {
        $min = 1;
        $max = 9;
        $length = 8;

        while ($length > 1) {
            $min .= 0;
            $max .= 9;

            $length--;
        }

        return [
            $min, $max,
        ];
    }
}
