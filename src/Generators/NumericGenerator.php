<?php

/*
 * @copyright 2021 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp\Generators;

use Erdemkeren\Otp\Contracts\GeneratorContract;
use Throwable;

class NumericGenerator implements GeneratorContract
{
    public function generate(int $length = null): string
    {
        $range = $this->generateRangeForLength($length);

        try {
            $int = random_int($range[0], $range[1]);
        } catch (Throwable) {
            $int = rand($range[0], $range[1]);
        }

        return (string) $int;
    }

    protected function generateRangeForLength(int $length): array
    {
        $min = 1;
        $max = 9;

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
