<?php

namespace Erdemkeren\TemporaryAccess\PasswordGenerators;

use Exception;
use Erdemkeren\TemporaryAccess\PasswordGeneratorInterface;

class NumericPasswordGenerator implements PasswordGeneratorInterface
{
    /**
     * Generate a numeric password.
     *
     * @param  int $length
     * @return string
     */
    public function generate(int $length): string
    {
        $range = $this->generateRangeForLength($length);

        try {
            $int = random_int($range[0], $range[1]);
        } catch (Exception $e) {
            $int = rand($range[0], $range[1]);
        }

        return (string) $int;
    }

    /**
     * Generate the required range for the given length.
     *
     * @param  int $length
     * @return array
     */
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
