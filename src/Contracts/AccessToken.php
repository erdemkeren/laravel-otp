<?php

namespace Erdemkeren\TemporaryAccess\Contracts;

interface AccessToken extends TokenInformation
{
    /**
     * Get the unique identifier of the authenticatable who owns the access token.
     *
     * @return string
     */
    public function authenticatableId();

    /**
     * Get the created at timestamp of the access token.
     *
     * @return \Carbon\Carbon
     */
    public function createdAt();

    /**
     * Get the expires at timestamp of the access token.
     *
     * @return \Carbon\Carbon
     */
    public function expiresAt();

    /**
     * Prolong the expire date of the access token.
     *
     * @param  int $prolong The prolong time in seconds.
     *
     * @return AccessToken
     */
    public function prolong($prolong);
}
