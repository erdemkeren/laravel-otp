<?php

namespace Erdemkeren\TemporaryAccess\Contracts;

interface TokenInformation
{
    /**
     * Get the token.
     *
     * @return string
     */
    public function token();

    /**
     * Get the token encrypted.
     * Alias of token.
     *
     * @return string
     */
    public function encrypted();

    /**
     * Get the access code as plain text.
     *
     * @return string
     */
    public function plain();

    /**
     * Get the access token as plain text.
     * Alias for plain.
     *
     * @return string
     */
    public function code();
}
