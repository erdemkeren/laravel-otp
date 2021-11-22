<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp\Contracts;

use Erdemkeren\Otp\OtpToken;

/**
 * Interface TokenRepositoryContract.
 */
interface TokenRepositoryContract
{
    /**
     * Save the given token in the storage.
     *
     * @param  OtpToken  $token
     * @return bool
     */
    public function persist(OtpToken $token): bool;
}
