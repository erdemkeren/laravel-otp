<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp;

use Carbon\Carbon;

class OtpToken
{
    public function __construct(private array $attributes)
    {
        if (! array_key_exists('created_at', $this->attributes)) {
            $this->attributes['created_at'] = $this->getNow();
            $this->attributes['updated_at'] = $this->getNow();
        }
    }

    public function plainText(): ?string
    {
        return $this->getAttributeValue('plain_text');
    }

    public function cipherText(): string
    {
        return $this->getAttributeValue('cipher_text');
    }

    public function format(): string
    {
        return $this->getAttributeValue('format');
    }

    public function expiryTime(): int
    {
        return $this->getAttributeValue('expiry_time');
    }

    public function timeLeft(): int
    {
        return $this->getNow()->diffInSeconds($this->expiresAt(), false);
    }

    public function expiresAt(): Carbon
    {
        return (clone $this->createdAt())->addSeconds($this->expiryTime());
    }

    public function authenticableId(): int|string
    {
        return $this->getAttributeValue('authenticable_id');
    }

    public function createdAt(): Carbon
    {
        return new Carbon($this->getAttributeValue('created_at'));
    }

    public function updatedAt(): Carbon
    {
        return new Carbon($this->getAttributeValue('updated_at'));
    }

    public function extend(int $secs): self
    {
        return new static(array_merge($this->attributes, [
            'expiry_time' => $this->expiryTime() + $secs,
            'updated_at' => $this->getNow(),
        ]));
    }

    public function refresh(): self
    {
        return $this->extend($this->getNow()->diffInSeconds($this->updatedAt()));
    }

    public function invalidate(): self
    {
        return new static(array_merge($this->attributes, [
            'expiry_time' => 0,
            'updated_at' => $this->getNow(),
        ]));
    }

    public function expired(): bool
    {
        return $this->timeLeft() <= 0;
    }

    public function withoutPlainText(): self
    {
        return new static(array_filter(array_merge($this->attributes, [
            'plain_text' => null,
        ])));
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    private function getNow(): Carbon
    {
        return Carbon::now();
    }

    private function getAttributeValue(string $key): mixed
    {
        return array_key_exists($key, $this->attributes)
            ? $this->attributes[$key]
            : null;
    }
}
