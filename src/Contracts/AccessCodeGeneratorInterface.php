<?php

namespace Erdemkeren\TemporaryAccess\Contracts;

interface AccessCodeGeneratorInterface
{
    /**
     * Generate a new access code.
     *
     * @param  int $length
     *
     * @return AccessCodeInterface
     */
    public function generate($length = 6);

    /**
     * Generate an access token from the given plain text.
     *
     * @param  string $plainText
     *
     * @return AccessCodeInterface
     */
    public function fromPlain($plainText);
}
