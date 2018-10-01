<?php

namespace Erdemkeren\TemporaryAccess;

use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;

interface TokenInterface
{
    public function authenticableId();
    public function notify(Notifiable $notifiable): void;
    public function cipherText(): string;
    public function plainText(): string;
    public function createdAt(): Carbon;
    public function updatedAt(): Carbon;
    public function expiresAt(): Carbon;
    public function timeLeft(): int;
    public function refresh(): bool;
    public function extend(?int $seconds = null): bool;
    public function persist(): bool;
}
