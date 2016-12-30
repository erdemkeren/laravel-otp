<?php

namespace Erdemkeren\TemporaryAccess\Contracts;

use stdClass;

interface AccessTokenRepositoryInterface
{
    /**
     * Retrieve an access token from the storage.
     *
     * @param  int    $authenticatableId The unique identifier of the authenticatable who owns the access token.
     * @param  string $code              The access code of the authenticatable.
     *
     * @return stdClass|array|bool
     */
    public function retrieve($authenticatableId, $code);

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
     * @param  int         $authenticatableId The unique identifier of the authenticatable who owns the access token.
     * @param  string      $code              The access token generated for the authenticatable.
     * @param  string|null $expires           The expiration date of the access token.
     *
     * @return array
     */
    public function store($authenticatableId, $code, $expires = null);

    /**
     * Update the expire date of the given access token in the storage.
     *
     * @param  int    $authenticatableId The unique identifier of the authenticatable.
     * @param  string $code              The encrypted access token to be updated.
     * @param  string $expires           The new expiration date of the access token.
     *
     * @return bool
     */
    public function update($authenticatableId, $code, $expires);

    /**
     * Delete a resource from the storage.
     *
     * @param  int    $authenticatableId The unique identifier of the authenticatable.
     * @param  string $code              The token to be deleted.
     *
     * @return int
     */
    public function delete($authenticatableId, $code);

    /**
     * Delete expired access tokens from the storage.
     *
     * @return void
     */
    public function deleteExpired();
}
