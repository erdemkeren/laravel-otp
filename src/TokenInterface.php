<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp;

use Carbon\Carbon;
use Illuminate\Notifications\Notification;

interface TokenInterface
{
    public const SCOPE_DEFAULT = 'default';

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
     * @return null|string
     */
    public function plainText(): ?string;

    /**
     * Get the expiry time of the token in seconds.
     *
     * @return int
     */
    public function expiryTime(): int;

    /**
     * Get the scope of the token.
     *
     * @return string
     */
    public function scope(): string;

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
     */
    public function revoke(): void;

    /**
     * Invalidate the token.
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
     * @param null|int $seconds
     *
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
     * @param string      $cipherText
     * @param null|string $plainText
     * @param null|int    $expiryTime
     * @param null|string $scope
     *
     * @return TokenInterface
     */
    public static function create(
        $authenticableId,
        string $cipherText,
        ?string $plainText = null,
        ?int $expiryTime = null,
        ?string $scope = null
    ): self;

    /**
     * Retrieve a token by the given attributes from the storage.
     *
     * @param array $attributes
     *
     * @return null|TokenInterface
     */
    public static function retrieveByAttributes(array $attributes): ?self;
}
