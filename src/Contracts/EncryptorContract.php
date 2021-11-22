<?php
/*
 * Copyright (c) 2021. Hilmi Erdem Keren
 * license MIT
 */

namespace Erdemkeren\Otp\Contracts;

/**
 * Interface EncryptorContract.
 */
interface EncryptorContract
{
    /**
     * Encrypt the given text.
     *
     * @param  string  $plainText
     * @return string
     */
    public function encrypt(string $plainText): string;
}
