<?php

namespace Erdemkeren\TemporaryAccess;

use Erdemkeren\TemporaryAccess\Contracts\AccessCodeGeneratorInterface;

final class AccessCodeGenerator implements AccessCodeGeneratorInterface
{
    /**
     * The key to be used to encrypt the access code.
     *
     * @var string
     */
    private $key;

    /**
     * AccessCodeGenerator constructor.
     *
     * @param string $key
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * Generate a new access code.
     *
     * @param  int $length
     *
     * @return AccessCode
     */
    public function generate($length = 6)
    {
        $plainText = str_random($length);

        return $this->makeCode($plainText, $this->encrypt($plainText));
    }

    /**
     * Generate an access code from the given plain text.
     *
     * @param  string $plainText
     *
     * @return AccessCode
     */
    public function fromPlain($plainText)
    {
        return $this->makeCode($plainText, $this->encrypt($plainText));
    }

    /**
     * Get the hashed version of the generated code.
     *
     * @param  string $plainText The plain text to be encrypted.
     *
     * @return string
     */
    private function encrypt($plainText)
    {
        return hash_hmac('sha256', $plainText, $this->key);
    }

    /**
     * Create a new code instance with the given parameters.
     *
     * @param  string $plainText     The plain text code.
     * @param  string $encryptedText The encrypted code.
     *
     * @return AccessCode
     */
    private function makeCode($plainText, $encryptedText)
    {
        return new AccessCode($plainText, $encryptedText);
    }
}
