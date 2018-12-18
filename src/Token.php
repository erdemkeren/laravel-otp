<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Notifications\Notification;

/**
 * Class Token.
 */
class Token implements TokenInterface
{
    /**
     * The attributes of the token.
     *
     * @var array
     */
    public $attributes = [
        'authenticable_id' => null,
        'cipher_text'      => null,
        'plain_text'       => null,
        'expiry_time'      => null,
        'scope'            => null,
        'created_at'       => null,
        'updated_at'       => null,
    ];

    /**
     * Token constructor.
     *
     * @param int|mixed|string $authenticableId
     * @param string           $cipherText
     * @param null|string      $plainText
     * @param null|string      $scope
     * @param null|int         $length
     * @param null|int         $expiryTime
     * @param null|string      $generator
     * @param null|Carbon      $createdAt
     * @param null|Carbon      $updatedAt
     */
    public function __construct(
        $authenticableId,
        string  $cipherText,
        ?string $plainText = null,
        ?string $scope = null,
        ?int    $length = null,
        ?int    $expiryTime = null,
        ?string $generator = null,
        ?Carbon $createdAt = null,
        ?Carbon $updatedAt = null
    ) {
        $now = $this->getNow();

        if (null === $authenticableId) {
            throw new \LogicException(
                'The unique identifier of token owner shall not be null.'
            );
        }
        $this->attributes['authenticable_id'] = $authenticableId;
        $this->attributes['plain_text'] = $plainText;
        $this->attributes['cipher_text'] = $cipherText;
        $this->attributes['scope'] = $scope;
        $this->attributes['length'] = $length;
        $this->attributes['expiry_time'] = $expiryTime;
        $this->attributes['generator'] = $generator;

        $this->attributes['created_at'] = $createdAt ?: $now;
        $this->attributes['updated_at'] = $updatedAt ?: $now;
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
     * @return null|string
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
        return clone $this->attributes['created_at'];
    }

    /**
     * Get the last update date of the token.
     *
     * @return Carbon
     */
    public function updatedAt(): Carbon
    {
        return clone $this->attributes['updated_at'];
    }

    /**
     * Get the scope of the token.
     *
     * @return string
     */
    public function scope(): string
    {
        return $this->attributes['scope'] ?: TokenInterface::SCOPE_DEFAULT;
    }

    /**
     * Get the length of the token.
     *
     * @return null|int
     */
    public function length(): ?int
    {
        return null === $this->attributes['length']
            ? $this->getDefaultPasswordLength()
            : $this->attributes['length'];
    }

    /**
     * Get the generator name which created the token.
     *
     * @return null|string
     */
    public function generator(): ?string
    {
        return $this->attributes['generator'] ?: $this->getDefaultPasswordGenerator();
    }

    /**
     * Get the expiry time of the token in seconds.
     *
     * @return int
     */
    public function expiryTime(): int
    {
        return null === $this->attributes['expiry_time']
            ? $this->getDefaultExpiryTime()
            : $this->attributes['expiry_time'];
    }

    /**
     * Get the date time the token will expire.
     *
     * @return Carbon
     */
    public function expiresAt(): Carbon
    {
        return (clone $this->createdAt())->addSeconds($this->expiryTime());
    }

    /**
     * Get the validity time left for the token.
     *
     * @return int
     */
    public function timeLeft(): int
    {
        return $this->getNow()->diffInSeconds($this->expiresAt(), false);
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
     * Alias for invalidate.
     */
    public function revoke(): void
    {
        $this->invalidate();
    }

    /**
     * Invalidate the token.
     */
    public function invalidate(): void
    {
        $this->attributes['expiry_time'] = 0;

        $this->persist();
    }

    /**
     * Extend the validity of the token.
     *
     * @param null|int $seconds
     *
     * @return bool
     */
    public function extend(?int $seconds = null): bool
    {
        $seconds = null === $seconds ? $this->getDefaultExpiryTime() : $seconds;

        $this->attributes['expiry_time'] = $this->expiryTime() + $seconds;

        return $this->persist();
    }

    /**
     * Refresh the token.
     *
     * @return bool
     */
    public function refresh(): bool
    {
        return $this->extend(
            $this->getNow()->diffInSeconds($this->updatedAt())
        );
    }

    /**
     * Create a new token.
     *
     * @param mixed       $authenticableId
     * @param string      $cipherText
     * @param null|string $plainText
     * @param null|string $scope
     * @param null|int    $length
     * @param null|int    $expiryTime
     * @param null|string $generator
     *
     * @return TokenInterface
     */
    public static function create(
        $authenticableId,
        string  $cipherText,
        ?string $plainText = null,
        ?string $scope = null,
        ?int $length = null,
        ?int $expiryTime = null,
        ?string $generator = null
    ): TokenInterface {
        $token = new static($authenticableId, $cipherText, $plainText, $scope, $length, $expiryTime, $generator);

        $token->persist();

        return $token;
    }

    /**
     * Retrieve a token by the given attributes from the storage.
     *
     * @param array $attributes
     *
     * @return null|TokenInterface
     */
    public static function retrieveByAttributes(array $attributes): ?TokenInterface
    {
        $query = DB::table(self::getTable());

        foreach ($attributes as $key => $value) {
            $query->where($key, $value);
        }

        if (! $entity = $query->first()) {
            return null;
        }

        return new static(
            $entity->authenticable_id,
            $entity->cipher_text,
            null,
            $entity->scope,
            $entity->length,
            $entity->expiry_time,
            $entity->generator,
            new Carbon($entity->created_at),
            new Carbon($entity->updated_at)
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
    protected function persist(): bool
    {
        $this->attributes['updated_at'] = $this->getNow();

        $attributes = $this->attributes;
        $attributes['created_at'] = $attributes['created_at']->toDateTimeString();
        $attributes['updated_at'] = $attributes['updated_at']->toDateTimeString();

        if (array_key_exists('plain_text', $attributes)) {
            unset($attributes['plain_text']);
        }

        try {
            DB::beginTransaction();

            DB::table(self::getTable())->updateOrInsert([
                'authenticable_id' => $attributes['authenticable_id'],
                'cipher_text'      => $attributes['cipher_text'],
                'scope'            => $attributes['scope'],
            ], $attributes);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw new \RuntimeException(
                'Something went wrong while saving the access token.',
                0,
                $e
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
        return config('otp.table');
    }

    /**
     * Get the default expiry time in seconds.
     *
     * @return int
     */
    private function getDefaultExpiryTime(): int
    {
        return config('otp.expires') * 60;
    }

    /**
     * Get the default expiry time in seconds.
     *
     * @return int
     */
    private function getDefaultPasswordGenerator(): int
    {
        return config('otp.password_generator');
    }

    /**
     * Get the default expiry time in seconds.
     *
     * @return int
     */
    private function getDefaultPasswordLength(): int
    {
        return config('otp.password_length');
    }
}
