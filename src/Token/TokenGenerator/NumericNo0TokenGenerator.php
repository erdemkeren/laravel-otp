<?php

namespace Erdemkeren\TemporaryAccess\Token\TokenGenerator;

use Exception;

final class NumericNo0TokenGenerator extends NumericTokenGenerator implements TokenGeneratorInterface
{
    protected function getPlainText($length)
    {
        $range = $this->generateRangeForLength($length);

        try {
            $int = random_int($range[0], $range[1]);
        } catch (Exception $e) {
            $int = rand($range[0], $range[1]);
        }

        return (string) str_replace(0, $this->getRandomDigitWithNo0(), (string) $int);
    }

    private function getRandomDigitWithNo0()
    {
        try {
            $int = random_int(1, 9);
        } catch (Exception $e) {
            $int = rand(1, 9);
        }

        return $int;
    }
}
