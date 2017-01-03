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
     * @param ConnectionInterface $connection The database connection interface.
     * @param string              $table      The name of the database table.
     * @param int                 $expires    The default expire time in minutes.
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
     * @param  int    $authenticatableId The unique identifier of the authenticatable who has the access.
     * @param  string $token             The encrypted token of the authenticatable.
     *
     * @return stdClass|array|bool
     */
    public function retrieve($authenticatableId, $token)
    {
        $token = $this->find($authenticatableId, $token)->first();

        return $this->filterExpiredAccessToken($token);
    }

    /**
     * Retrieve the first valid resource by the given attributes.
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

        $token = $query->first($attributes);

        return $this->filterExpiredAccessToken($token);
    }

    /**
     * Store a new access token in the storage.
     *
     * @param  int         $authenticatableId The unique identifier of the authenticatable who has the access.
     * @param  string      $token             The encrypted token of the authenticatable.
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
     * @param  string $token             The encrypted token to be updated.
     * @param  string $expiresAt         The new expiration date of the access token.
     *
     * @return bool
     */
    public function update($authenticatableId, $token, $expiresAt)
    {
        return (bool) $this->find($authenticatableId, $token)->update([
            'expires_at' => (string) $expiresAt,
        ]);
    }

    /**
     * Delete a resource from the storage.
     *
     * @param  int    $authenticatableId The unique identifier of the authenticatable.
     * @param  string $token             The encrypted token of the authenticatable.
     *
     * @return int
     */
    public function delete($authenticatableId, $token)
    {
        return (bool) $this->find($authenticatableId, $token)->delete();
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
     * Get a new find query.
     *
     * @param  int    $authenticatableId
     * @param  string $token
     *
     * @return \Illuminate\Database\Query\Builder
     */
    private function find($authenticatableId, $token)
    {
        return $this->getTable()->where('authenticatable_id', $authenticatableId)->where('token', $token);
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
        $expiresAt = $expiresAt ? $expiresAt : $this->makeExpiresAt();

        $payload = [
            'authenticatable_id' => $authenticatableId,
            'token'              => $token,
            'created_at'         => (string) $this->getNow(),
            'expires_at'         => (string) $expiresAt,
        ];

        return $payload;
    }

    /**
     * Filter the given database response and only return if it is not expired.
     *
     * @param stdClass|array|null $accessToken
     *
     * @return stdClass|array|null
     */
    private function filterExpiredAccessToken($accessToken)
    {
        if ($accessToken && ! $this->accessTokenExpired((object) $accessToken)) {
            return $accessToken;
        }
    }

    /**
     * Determine if the token has expired.
     *
     * @param  stdClass $accessToken
     *
     * @return bool
     */
    private function accessTokenExpired(stdClass $accessToken)
    {
        $expiresAt = $accessToken->expires_at ? new Carbon($accessToken->expires_at) : $this->makeExpiresAt();

        return $this->getNow()->gte($expiresAt);
    }

    /**
     * Make the expires at property from configuration.
     *
     * @return Carbon
     */
    private function makeExpiresAt()
    {
        return $this->getFuture($this->expires);
    }

    /**
     * Get the current time.
     *
     * @return Carbon
     */
    private function getNow()
    {
        return Carbon::now();
    }

    /**
     * Get the time after given minutes.
     *
     * @param  int $minutesLater
     *
     * @return Carbon
     */
    private function getFuture($minutesLater)
    {
        return $this->getNow()->addMinutes($minutesLater);
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
