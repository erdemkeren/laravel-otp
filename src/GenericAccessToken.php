<?php

namespace Erdemkeren\TemporaryAccess;

use Carbon\Carbon;
use LogicException;
use Erdemkeren\TemporaryAccess\Contracts\AccessToken as AccessTokenContract;

final class GenericAccessToken implements AccessTokenContract
{
    /**
     * The attributes of the access token.
     *
     * @var array
     */
    private $attributes;

    /**
     * GenericAccessToken constructor.
     *
     * @param  array $attributes
     */
    public function __construct(array $attributes)
    {
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
     * Get the access code as plain text.
     *
     * @return string
     * @throws LogicException
     */
    public function plain()
    {
        if (array_key_exists('plain', $this->attributes)) {
            return $this->getAttributeValue('plain');
        }

        $message = 'The plain text is not available at this state.';
        throw new LogicException($message);
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
     * Get the token.
     *
     * @return string
     */
    public function token()
    {
        return $this->getAttributeValue('token');
    }

    /**
     * Get the token encrypted.
     *
     * @return string
     */
    public function encrypted()
    {
        return $this->getAttributeValue('token');
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
     * @param  int $prolong
     *
     * @return AccessTokenContract
     */
    public function prolong($prolong)
    {
        return new static([
            'authenticatable_id' => $this->authenticatableId(),
            'token'              => $this->token(),
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
}
