<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp;

use Carbon\Carbon;

/**
 * Class OtpToken.
 */
class OtpToken
{
    /**
     * OtpToken constructor.
     *
     * @param  array  $attributes
     */
    public function __construct(private array $attributes)
    {
        if (! array_key_exists('created_at', $this->attributes)) {
            $this->attributes['created_at'] = $this->getNow();
            $this->attributes['updated_at'] = $this->getNow();
        }
    }

    /**
     * Get the plain text.
     *
     * @return string|null
     */
    public function plainText(): ?string
    {
        return $this->getAttributeValue('plain_text');
    }

    /**
     * Get the cipher text of the token.
     *
     * @return string
     */
    public function cipherText(): string
    {
        return $this->getAttributeValue('cipher_text');
    }

    /**
     * Get the expiry time of the token.
     *
     * @return int
     */
    public function expiryTime(): int
    {
        return $this->getAttributeValue('expiry_time');
    }

    /**
     * Get the time left for the expiration.
     *
     * @return int
     */
    public function timeLeft(): int
    {
        return $this->getNow()->diffInSeconds($this->expiresAt(), false);
    }

    /**
     * Get the expiry date.
     *
     * @return Carbon
     */
    public function expiresAt(): Carbon
    {
        return (clone $this->createdAt())->addSeconds($this->expiryTime());
    }

    /**
     * Get the authenticable id.
     *
     * @return int|string
     */
    public function authenticableId(): int|string
    {
        return $this->getAttributeValue('authenticable_id');
    }

    /**
     * Get the date token was created.
     *
     * @return Carbon
     */
    public function createdAt(): Carbon
    {
        return new Carbon($this->getAttributeValue('created_at'));
    }

    /**
     * Get the last date the token was updated.
     *
     * @return Carbon
     */
    public function updatedAt(): Carbon
    {
        return new Carbon($this->getAttributeValue('updated_at'));
    }

    /**
     * Get a new instance of the token
     * with an extended expiry time.
     *
     * @param  int  $secs
     * @return $this
     */
    public function extend(int $secs): self
    {
        return new static(array_merge($this->attributes, [
            'expiry_time' => $this->expiryTime() + $secs,
            'updated_at' => $this->getNow(),
        ]));
    }

    /**
     * Get a new instance of the token
     * with a refreshed expiry time.
     */
    public function refresh(): self
    {
        return $this->extend($this->getNow()->diffInSeconds($this->updatedAt()));
    }

    /**
     * Get a new instance of the token
     * that is expired.
     */
    public function invalidate(): self
    {
        return new static(array_merge($this->attributes, [
            'expiry_time' => 0,
            'updated_at' => $this->getNow(),
        ]));
    }

    /**
     * Determine if the token is expired or not.
     *
     * @return bool
     */
    public function expired(): bool
    {
        return $this->timeLeft() <= 0;
    }

    /**
     * Get the token without the plain text.
     *
     * return self
     */
    public function withoutPlainText(): self
    {
        return new static(array_filter(array_merge($this->attributes, [
            'plain_text' => null,
        ])));
    }

    /**
     * Get the array representation of the token.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->attributes;
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
     * Get the value of an attribute.
     *
     * @param  string  $key
     * @return mixed
     */
    private function getAttributeValue(string $key): mixed
    {
        return array_key_exists($key, $this->attributes)
            ? $this->attributes[$key]
            : null;
    }
}
