<?php

namespace Erdemkeren\TemporaryAccess;

use Erdemkeren\TemporaryAccess\Contracts\AccessCode as Contract;

final class AccessCode implements Contract
{
    /**
     * The plain text.
     *
     * @var string
     */
    private $plainText;

    /**
     * The encrypted text.
     *
     * @var string
     */
    private $encryptedText;

    /**
     * AccessCode constructor.
     *
     * @param  string $plainText
     * @param  string $encryptedText
     */
    public function __construct($plainText, $encryptedText)
    {
        $this->plainText = $plainText;
        $this->encryptedText = $encryptedText;
    }

    /**
     * Get the access code as plain text.
     *
     * @return string
     */
    public function plain()
    {
        return $this->plainText;
    }

    /**
     * Get the access token as plain text.
     * Alias for plain.
     *
     * @return string
     */
    public function code()
    {
        return $this->plain();
    }

    /**
     * Get the access code encrypted.
     *
     * @return string
     */
    public function encrypted()
    {
        return $this->encryptedText;
    }

    /**
     * Get the token.
     *
     * @return string
     */
    public function token()
    {
        return $this->encrypted();
    }

    /**
     * Convert the access code to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->encrypted();
    }
}
