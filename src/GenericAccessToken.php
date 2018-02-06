<?php

namespace Erdemkeren\TemporaryAccess;

use Carbon\Carbon;
use LogicException;
use Erdemkeren\TemporaryAccess\Token\TokenInterface;
use Erdemkeren\TemporaryAccess\Contracts\AccessTokenInterface;

final class GenericAccessToken implements AccessTokenInterface
{
    /**
     * The token.
     *
     * @var TokenInterface
     */
    private $token;

    /**
     * The attributes of the access token.
     *
     * @var array
     */
    private $attributes;

    /**
     * GenericAccessToken constructor.
     *
     * @param  TokenInterface $token The access token.
     * @param  array $attributes     The attributes of the access token.
     */
    public function __construct(TokenInterface $token, array $attributes)
    {
        $this->token = $token;
        $this->attributes = $attributes;
    }

    /**
     * Get the unique identifier of the authenticatable who owns the access token.
     *
     * @return string
     */
    public function authenticatableId()
    {
        return $this->getAttributeValue('authenticatable_id');
    }

    /**
     * Get the token.
     *
     * @return TokenInterface
     */
    public function token()
    {
        return $this->token;
    }

    /**
     * Get the access token as plain text.
     *
     * @return string
     * @throws LogicException
     */
    public function plain()
    {
        if (! $plainText = $this->token->plain()) {
            $message = 'The plain text is not available at this state.';
            throw new LogicException($message);
        }

        return $plainText;
    }

    /**
     * Get the access token encrypted.
     *
     * @return string
     */
    public function encrypted()
    {
        return (string) $this->token();
    }

    /**
     * Get the created at timestamp of the access token.
     *
     * @return \Carbon\Carbon
     */
    public function createdAt()
    {
        return new Carbon($this->getAttributeValue('created_at'));
    }

    /**
     * Get the expires at timestamp of the access token.
     *
     * @return \Carbon\Carbon
     */
    public function expiresAt()
    {
        return new Carbon($this->getAttributeValue('expires_at'));
    }

    /**
     * Get a new instance of the access token with a longer expire date.
     *
     * @param  int $prolong The prolong time in seconds.
     *
     * @return GenericAccessToken
     */
    public function prolong($prolong)
    {
        return new static($this->token(), [
            'authenticatable_id' => $this->authenticatableId(),
            'created_at'         => $this->createdAt(),
            'expires_at'         => (string) $this->expiresAt()->addSeconds($prolong),
        ]);
    }

    /**
     * Get the value of the given key.
     *
     * @param  string $key
     *
     * @return mixed
     */
    private function getAttributeValue($key)
    {
        return $this->attributes[$key];
    }

    /**
     * Convert the access token to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->encrypted();
    }
}
