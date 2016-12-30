<?php

namespace Erdemkeren\TemporaryAccess;

use stdClass;
use Carbon\Carbon;
use Illuminate\Database\ConnectionInterface;
use Erdemkeren\TemporaryAccess\Contracts\AccessTokenRepositoryInterface;

final class DatabaseAccessTokenRepository implements AccessTokenRepositoryInterface
{
    /**
     * The connection.
     *
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * The temporary access token table.
     *
     * @var string
     */
    private $table;

    /**
     * The number of seconds a token should last.
     *
     * @var int
     */
    private $expires;

    /**
     * DatabaseAccessTokenRepository constructor.
     *
     * @param ConnectionInterface $connection
     * @param string             $table
     * @param int                $expires
     */
    public function __construct(ConnectionInterface $connection, $table, $expires)
    {
        $this->table = $table;
        $this->expires = $expires;
        $this->connection = $connection;
    }

    /**
     * Retrieve an access token from the storage.
     *
     * @param  int    $authenticatableId The unique identifier of the authenticatable who owns the access token.
     * @param  string $token             The access code of the authenticatable.
     *
     * @return stdClass|array|bool
     */
    public function retrieve($authenticatableId, $token)
    {
        $token = $this->getTable()->where('authenticatable_id', $authenticatableId)->where('token', $token)->first();

        if ($token && ! $this->tokenExpired((object) $token)) {
            return $token;
        }

        return false;
    }

    /**
     * Retrieve the first resource by the given attributes.
     *
     * @param  array $queryParams The key - value pairs to match.
     * @param  array $attributes  The attributes to be returned from the storage.
     *
     * @return stdClass|array|null
     */
    public function retrieveByAttributes(array $queryParams, array $attributes = ['*'])
    {
        $query = $this->getTable();

        foreach ($queryParams as $column => $value) {
            $query = $query->where($column, $value);
        }

        $resource = $query->first($attributes);

        if (! $resource || $this->tokenExpired($resource)) {
            return;
        }

        return $resource;
    }

    /**
     * Store a new access token in the storage.
     *
     * @param  int         $authenticatableId The unique identifier of the authenticatable who owns the access token.
     * @param  string      $token             The access code generated for the authenticatable.
     * @param  string|null $expiresAt         The expiration date of the access token.
     *
     * @return array
     */
    public function store($authenticatableId, $token, $expiresAt = null)
    {
        $payload = $this->getAccessTokenPayload($authenticatableId, $token, $expiresAt);

        $id = $this->getTable()->insertGetId($payload);
        $payload['id'] = $id;

        return $payload;
    }

    /**
     * Update the expire date of the given access token in the storage.
     *
     * @param  int    $authenticatableId The unique identifier of the authenticatable.
     * @param  string $token             The encrypted access code to be updated.
     * @param  string $expires           The new expiration date of the access token.
     *
     * @return bool
     */
    public function update($authenticatableId, $token, $expires)
    {
        return (bool) $this->getTable()->where('authenticatable_id', $authenticatableId)->where('token', $token)->update([
            'expires_at' => (string) $expires,
        ]);
    }

    /**
     * Delete a resource from the storage.
     *
     * @param  int    $authenticatableId The unique identifier of the authenticatable.
     * @param  string $token             The code of the authenticatable.
     *
     * @return bool
     */
    public function delete($authenticatableId, $token)
    {
        return (bool) $this->getTable()->where('authenticatable_id', $authenticatableId)->where('token', $token)->delete();
    }

    /**
     * Delete expired access tokens from the storage.
     *
     * @return void
     */
    public function deleteExpired()
    {
        $this->getTable()->where('expires_at', '<=', (string) $this->getNow())->delete();
    }

    /**
     * Get an access token payload.
     *
     * @param  int         $authenticatableId
     * @param  string      $token
     * @param  string|null $expiresAt
     *
     * @return array
     */
    private function getAccessTokenPayload($authenticatableId, $token, $expiresAt)
    {
        $expiresAt = $expiresAt ? $expiresAt : $this->getNow()->addMinutes($this->expires);

        $payload = [
            'authenticatable_id' => $authenticatableId,
            'token'              => $token,
            'created_at'         => (string) $this->getNow(),
            'expires_at'         => (string) $expiresAt,
        ];

        return $payload;
    }

    /**
     * Determine if the token has expired.
     *
     * @param  stdClass $token
     *
     * @return bool
     */
    private function tokenExpired(stdClass $token)
    {
        $expiresAt = $token->expires_at ? new Carbon($token->expires_at) : $this->getNow()->addMinutes($this->expires);

        return $this->getNow()->gte($expiresAt);
    }

    /**
     * Get the current UNIX timestamp.
     *
     * @return Carbon
     */
    private function getNow()
    {
        return Carbon::now();
    }

    /**
     * Begin a new database query against the table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    private function getTable()
    {
        return $this->getConnection()->table($this->table);
    }

    /**
     * Get the database connection instance.
     *
     * @return ConnectionInterface
     */
    private function getConnection()
    {
        return $this->connection;
    }
}
