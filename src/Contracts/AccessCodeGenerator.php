<?php

namespace Erdemkeren\TemporaryAccess\Contracts;

interface AccessCodeGenerator
{
    /**
     * Generate a new access code.
     *
     * @param  int $length
     *
     * @return AccessCode
     */
    public function generate($length = 6);

    /**
     * Generate an access token from the given plain text.
     *
     * @param  string $plainText
     *
     * @return AccessCode
     */
    public function fromPlain($plainText);
}
