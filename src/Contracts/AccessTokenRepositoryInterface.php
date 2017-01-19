<?php

namespace Erdemkeren\TemporaryAccess\Contracts;

use stdClass;

interface AccessTokenRepositoryInterface
{
    /**
     * Retrieve an access token from the storage.
     *
     * @param  int    $authenticatableId The unique identifier of the authenticatable who has the access.
     * @param  string $token             The encrypted token of the authenticatable.
     *
     * @return stdClass|array|bool
     */
    public function retrieve($authenticatableId, $token);

    /**
     * Retrieve the first valid resource by the given attributes.
     *
     * @param  array $queryParams The key - value pairs to match.
     * @param  array $attributes  The attributes to be returned from the storage.
     *
     * @return stdClass|array|null
     */
    public function retrieveByAttributes(array $queryParams, array $attributes = ['*']);

    /**
     * Store a new access token in the storage.
     *
     * @param  int         $authenticatableId The unique identifier of the authenticatable who has the access.
     * @param  string      $token             The encrypted token of the authenticatable.
     * @param  string|null $expiresAt         The expiration date of the access token.
     *
     * @return array
     */
    public function store($authenticatableId, $token, $expiresAt = null);

    /**
     * Update the expire date of the given access token in the storage.
     *
     * @param  int    $authenticatableId The unique identifier of the authenticatable.
     * @param  string $token             The encrypted token to be updated.
     * @param  string $expiresAt         The new expiration date of the access token.
     *
     * @return bool
     */
    public function update($authenticatableId, $token, $expiresAt);

    /**
     * Delete a resource from the storage.
     *
     * @param  string $token The encrypted token of the authenticatable.
     *
     * @return int
     */
    public function delete($token);

    /**
     * Delete expired access tokens from the storage.
     *
     * @return void
     */
    public function deleteExpired();
}
