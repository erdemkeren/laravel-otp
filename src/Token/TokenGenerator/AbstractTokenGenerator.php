<?php

namespace Erdemkeren\TemporaryAccess\Token\TokenGenerator;

use Erdemkeren\TemporaryAccess\Token\GenericToken;

abstract class AbstractTokenGenerator implements TokenGeneratorInterface
{
    /**
     * The key to be used to encrypt the plain token.
     *
     * @var string
     */
    private $key;

    /**
     * TokenGenerator constructor.
     *
     * @param string $key    The key to be used by the hash algorithm.
     * @param int    $length The length of the tokens being generated.
     */
    public function __construct($key, $length = 6)
    {
        $this->key = $key;
    }

    /**
     * Generate a new token.
     *
     * @param  int $length The length of the plain text to be generated.
     *
     * @return GenericToken
     */
    public function generate($length = 6)
    {
        $plainText = $this->getPlainText($length);

        return $this->makeToken($this->encrypt($plainText), $plainText);
    }

    /**
     * Generate a token from the given plain text.
     *
     * @param  string $plainText The plain text.
     *
     * @return GenericToken
     */
    public function fromPlain($plainText)
    {
        return $this->makeToken($this->encrypt($plainText), $plainText);
    }

    /**
     * Generate a token from the given encrypted text.
     *
     * @param  string $encryptedText The encrypted text.
     *
     * @return GenericToken
     */
    public function fromEncrypted($encryptedText)
    {
        return $this->makeToken($encryptedText);
    }

    /**
     * Get the hashed version of the generated token.
     *
     * @param  string $plainText
     *
     * @return string
     */
    private function encrypt($plainText)
    {
        return hash_hmac('sha256', $plainText, $this->key);
    }

    /**
     * Create a new token instance with the given parameters.
     *
     * @param  string      $encryptedText
     * @param  string|null $plainText
     *
     * @return GenericToken
     */
    private function makeToken($encryptedText, $plainText = null)
    {
        return new GenericToken($encryptedText, $plainText);
    }

    /**
     * Get the plain text version of the token being generated.
     *
     * @param  int $length
     *
     * @return string
     */
    abstract protected function getPlainText(int $length): string;
}