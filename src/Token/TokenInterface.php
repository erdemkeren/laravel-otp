<?php

namespace Erdemkeren\TemporaryAccess\Token;

interface TokenInterface
{
    /**
     * Get the token as encrypted text.
     *
     * @return string
     */
    public function encrypted();

    /**
     * Get the token as plain text.
     *
     * @return string|null
     */
    public function plain();

    /**
     * Convert the token to string.
     *
     * @return string
     */
    public function __toString();
}
