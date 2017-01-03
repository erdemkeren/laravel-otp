<?php

namespace Erdemkeren\TemporaryAccess;

use Erdemkeren\TemporaryAccess\Contracts\TokenInterface;

final class GenericToken implements TokenInterface
{
    /**
     * The plain text.
     *
     * @var string|null
     */
    private $plainText;

    /**
     * The encrypted text.
     *
     * @var string
     */
    private $encryptedText;

    /**
     * GenericToken constructor.
     *
     * @param  string      $encryptedText The encrypted text.
     * @param  string|null $plainText     The plain text string.
     */
    public function __construct($encryptedText, $plainText = null)
    {
        $this->plainText = $plainText;
        $this->encryptedText = $encryptedText;
    }

    /**
     * Get the token as plain text.
     *
     * @return string|null
     */
    public function plain()
    {
        return $this->plainText;
    }

    /**
     * Get the token as encrypted text.
     *
     * @return string
     */
    public function encrypted()
    {
        return $this->encryptedText;

    }

    /**
     * Convert the token to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->encrypted();
    }
}
