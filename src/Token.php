<?php

namespace Erdemkeren\TemporaryAccess;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Notifications\Notification;

final class Token implements TokenInterface
{
    public $attributes = [
        'authenticable_id' => null,
        'plain_text' => null,
        'cipher_text' => null,
        'created_at' => null,
        'updated_at' => null,
        'expires_at' => null,
    ];

    public function __construct(
        $authenticableId,
        string $cipherText,
        ?string $plainText = null,
        ?Carbon $createdAt = null,
        ?Carbon $updatedAt = null,
        ?Carbon $expiresAt = null
    )
    {
        $now = $this->getNow();

        $this->attributes['authenticable_id'] = $authenticableId;
        $this->attributes['plain_text'] = $plainText;
        $this->attributes['cipher_text'] = $cipherText;
        $this->attributes['created_at'] = $createdAt ?: $now;
        $this->attributes['updated_at'] = $updatedAt ?: $now;
        $this->attributes['expires_at'] = $expiresAt ?: $this->getNow()->addSeconds($this->getDefaultExpiryTime());
    }

    /**
     * Get the unique identifier of the authenticable
     * who owns the token.
     *
     * @return mixed
     */
    public function authenticableId()
    {
        return $this->attributes['authenticable_id'];
    }

    /**
     * Get the token as cipher text.
     *
     * @return string
     */
    public function cipherText(): string
    {
        return $this->attributes['cipher_text'];
    }

    /**
     * Get the token as plain text.
     *
     * @return string|null
     */
    public function plainText(): ?string
    {
        return $this->attributes['plain_text'];
    }

    /**
     * Get the date token created.
     *
     * @return Carbon
     */
    public function createdAt(): Carbon
    {
        return $this->attributes['created_at'];
    }

    /**
     * Get the last update date of the token.
     *
     * @return Carbon
     */
    public function updatedAt(): Carbon
    {
        return $this->attributes['updated_at'];
    }

    /**
     * Get the date time the token will expire.
     *
     * @return Carbon
     */
    public function expiresAt(): Carbon
    {
        return $this->attributes['expires_at'];
    }

    /**
     * Get the validity time left for the token.
     *
     * @return int
     */
    public function timeLeft(): int
    {
        return $this->expiresAt()->diffInSeconds($this->getNow(), false);
    }

    /**
     * Determine if the token is expired or not.
     *
     * @return bool
     */
    public function expired(): bool
    {
        return $this->timeLeft() > 0;
    }

    /**
     * Alias for invalidate.
     *
     * @return void
     */
    public function revoke(): void
    {
        $this->invalidate();
    }

    /**
     * Invalidate the token.
     *
     * @return void
     */
    public function invalidate(): void
    {
        $this->attributes['expires_at'] = Carbon::createFromTimestamp(0);

        $this->persist();
    }

    /**
     * Extend the validity of the token.
     *
     * @param int|null $seconds
     * @return bool
     */
    public function extend(?int $seconds = null): bool
    {
        $seconds = $seconds ?: $this->getDefaultExpiryTime();

        $this->attributes['expires_at']->addSeconds($seconds);

        return $this->persist();
    }

    /**
     * Refresh the token.
     *
     * @return bool
     */
    public function refresh(): bool
    {
        return $this->extend($this->getNow()->diffInSeconds($this->updatedAt()));
    }

    /**
     * Create a new token.
     *
     * @param $authenticableId
     * @param string $cipherText
     * @param null|string $plainText
     * @return TokenInterface
     */
    public static function create(
        $authenticableId,
        string $cipherText,
        ?string $plainText = null
    ): TokenInterface
    {
        $token = new Token($authenticableId, $cipherText, $plainText);

        $token->persist();

        return $token;
    }

    /**
     * Retrieve a token by the given attributes from the storage.
     *
     * @param  array $attributes
     *
     * @return TokenInterface|null
     */
    public static function retrieveByAttributes(array $attributes): ?TokenInterface
    {
        $query = DB::table(self::getTable());

        foreach ($attributes as $key => $value) {
            $query->where($key, $value);
        }

        if (!$entity = $query->first()) {
            return null;
        }

        return new static(
            $entity->authenticable_id,
            $entity->cipher_text,
            null,
            new Carbon($entity->created_at),
            new Carbon($entity->updated_at),
            new Carbon($entity->expires_at)
        );
    }


    /**
     * Convert the token to a token notification.
     *
     * @return Notification
     */
    public function toNotification(): Notification
    {
        return new TokenNotification($this);
    }

    /**
     * Persist the token in the storage.
     *
     * @return bool
     */
    private function persist(): bool
    {
        $this->attributes['created_at'] = $this->attributes['created_at'] ?: $this->getNow();
        $this->attributes['updated_at'] = $this->getNow();

        $attributes = $this->attributes;

        if (array_key_exists('plain_text', $attributes)) {
            unset($attributes['plain_text']);
        }

        try {
            DB::beginTransaction();

            DB::table(self::getTable())->updateOrInsert([
                'authenticable_id' => $this->authenticableId(),
                'cipher_text' => $this->cipherText(),
            ], $attributes);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw new \RuntimeException(
                'Something went wrong while updating the access token.', 0, $e
            );
        }

        return true;
    }

    /**
     * Get the date time at the moment.
     *
     * @return Carbon
     */
    private function getNow(): Carbon
    {
        return Carbon::now();
    }

    /**
     * Get the name of the table token will be persisted.
     *
     * @return string
     */
    private static function getTable(): string
    {
        return config('temporary_access.table');
    }

    /**
     * Get the default expiry time.
     *
     * @return int
     */
    private function getDefaultExpiryTime(): int
    {
        return config('temporary_access.expires') * 60;
    }

    /**
     * Convert the token to string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->cipherText();
    }
}
