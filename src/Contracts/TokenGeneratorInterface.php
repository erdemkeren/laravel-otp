<?php

namespace Erdemkeren\TemporaryAccess\Contracts;

interface TokenGeneratorInterface
{
    /**
     * Generate a new token.
     *
     * @param  int $length The length of the plain text to be generated.
     *
     * @return TokenInterface
     */
    public function generate($length = 6);

    /**
     * Generate a token from the given plain text.
     *
     * @param  string $plainText The plain text.
     *
     * @return TokenInterface
     */
    public function fromPlain($plainText);

    /**
     * Generate a token from the given encrypted text.
     *
     * @param  string $encryptedText The encrypted text.
     *
     * @return TokenInterface
     */
    public function fromEncrypted($encryptedText);
}
