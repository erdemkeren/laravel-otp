<?php

/*
 * @copyright 2018 Hilmi Erdem KEREN
 * @license MIT
 */

namespace Erdemkeren\Otp\Test;

use Erdemkeren\Otp\Encryptor;
use PHPUnit\Framework\TestCase;

class EncryptorTest extends TestCase
{
    public function testEncrypt(): void
    {
        $key = '12345678901234567890123456789012';

        $encryptor = new Encryptor($key);

        $cipherText = $encryptor->encrypt('foo');

        $this->assertSame(
            '17ee1a4ff9c322e0c4c7370d09a3c334ae78b97e6f37a918f8a1f1c906a85e93',
            $cipherText
        );
    }
}
