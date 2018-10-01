<?php

namespace Erdemkeren\TemporaryAccess;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Notification;

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

    public function notify(Notifiable $notifiable): void
    {
        Notification::send($notifiable, new TokenGenerated($this));
    }

    public function authenticableId()
    {
        return $this->attributes['authenticable_id'];
    }

    public function cipherText(): string
    {
        return $this->attributes['cipher_text'];
    }

    public function plainText(): string
    {
        return $this->attributes['plain_text'];
    }

    public function createdAt(): Carbon
    {
        return $this->attributes['created_at'];
    }

    public function updatedAt(): Carbon
    {
        return $this->attributes['updated_at'];
    }

    public function expiresAt(): Carbon
    {
        return $this->attributes['expires_at'];
    }

    public function timeLeft(): int
    {
        return $this->expiresAt()->diffInSeconds($this->getNow());
    }

    public function extend(?int $seconds = null): bool
    {
        $seconds = $seconds ?: $this->getDefaultExpiryTime();

        $this->attributes['expires_at']->addSeconds($seconds);

        return $this->persist();
    }

    public function refresh(): bool
    {
        return $this->extend($this->getNow()->diffInSeconds($this->updatedAt()));
    }

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

    public static function findByAttributes(array $attributes): ?TokenInterface
    {
        $query = DB::table(config('temporary_access.table'));

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

            DB::table(config('temporary_access.table'))->updateOrInsert([
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

    private function getNow(): Carbon
    {
        return Carbon::now();
    }

    private function getDefaultExpiryTime(): int
    {
        return config('temporary_access.expires') * 60;
    }
}
