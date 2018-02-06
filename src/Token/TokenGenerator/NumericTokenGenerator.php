<?php

namespace Erdemkeren\TemporaryAccess\Token\TokenGenerator;

use Exception;

class NumericTokenGenerator extends AbstractTokenGenerator implements TokenGeneratorInterface
{
    protected function getPlainText($length)
    {
        $range = $this->generateRangeForLength($length);

        try {
            $int = random_int($range[0], $range[1]);
        } catch (Exception $e) {
            $int = rand($range[0], $range[1]);
        }

        return (string) $int;
    }

    protected function generateRangeForLength($length)
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
