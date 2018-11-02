<?php

namespace Erdemkeren\TemporaryAccess;

use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;

interface TokenInterface
{
    /**
     * Get the unique identifier of the authenticable
     * who owns the token.
     *
     * @return mixed
     */
    public function authenticableId();

    /**
     * Get the token as cipher text.
     *
     * @return string
     */
    public function cipherText(): string;

    /**
     * Get the token as plain text.
     *
     * @return string|null
     */
    public function plainText(): ?string;

    /**
     * Get the date token created.
     *
     * @return Carbon
     */
    public function createdAt(): Carbon;

    /**
     * Get the last update date of the token.
     *
     * @return Carbon
     */
    public function updatedAt(): Carbon;

    /**
     * Get the date time the token will expire.
     *
     * @return Carbon
     */
    public function expiresAt(): Carbon;

    /**
     * Get the validity time left for the token.
     *
     * @return int
     */
    public function timeLeft(): int;

    /**
     * Determine if the token is expired or not.
     *
     * @return bool
     */
    public function expired(): bool;

    /**
     * Alias for invalidate.
     *
     * @return void
     */
    public function revoke(): void;

    /**
     * Invalidate the token.
     *
     * @return void
     */
    public function invalidate(): void;

    /**
     * Refresh the token.
     *
     * @return bool
     */
    public function refresh(): bool;

    /**
     * Extend the validity of the token.
     *
     * @param int|null $seconds
     * @return bool
     */
    public function extend(?int $seconds = null): bool;

    /**
     * Convert the token to a token notification.
     *
     * @return Notification
     */
    public function toNotification(): Notification;

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
    ): TokenInterface;

    /**
     * Retrieve a token by the given attributes from the storage.
     *
     * @param  array $attributes
     *
     * @return TokenInterface|null
     */
    public static function retrieveByAttributes(array $attributes): ?TokenInterface;
}
