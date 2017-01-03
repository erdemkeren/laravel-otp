<?php

namespace Erdemkeren\TemporaryAccess;

use Carbon\Carbon;
use Erdemkeren\TemporaryAccess\Contracts\TokenInterface;
use Erdemkeren\TemporaryAccess\Contracts\AccessTokenInterface;
use Erdemkeren\TemporaryAccess\Contracts\TokenGeneratorInterface;
use Erdemkeren\TemporaryAccess\Contracts\AccessTokenRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

final class TemporaryAccessService
{
    /**
     * The access token repository implementation.
     *
     * @var AccessTokenRepositoryInterface
     */
    private $repository;

    /**
     * The token generator implementation.
     *
     * @var TokenGeneratorInterface
     */
    private $tokenGenerator;

    /**
     * TemporaryAccessService constructor.
     *
     * @param AccessTokenRepositoryInterface $repository     The access token repository implementation.
     * @param TokenGeneratorInterface        $tokenGenerator The token generator implementation.
     */
    public function __construct(AccessTokenRepositoryInterface $repository, TokenGeneratorInterface $tokenGenerator)
    {
        $this->repository = $repository;
        $this->tokenGenerator = $tokenGenerator;
    }

    /**
     * Retrieve an access token from the storage by the actual token.
     *
     * @param AuthenticatableContract $authenticatable The authenticatable who owns the token.
     * @param string|TokenInterface   $encryptedText   The token of the authenticatable.
     *
     * @return null|AccessTokenInterface
     */
    public function retrieve(AuthenticatableContract $authenticatable, $encryptedText)
    {
        $authenticatableIdentifier = $authenticatable->getAuthIdentifier();

        return $this->retrieveFromRepository($authenticatableIdentifier, (string) $encryptedText);
    }

    /**
     * Retrieve an access token from the storage by the plain token.
     *
     * @param AuthenticatableContract $authenticatable The authenticatable who owns the token.
     * @param string                  $plainText       The token of the authenticatable.
     *
     * @return null|AccessTokenInterface
     */
    public function retrieveUsingPlainText(AuthenticatableContract $authenticatable, $plainText)
    {
        if (! $plainText instanceof TokenInterface) {
            $plainText = $this->makeTokenFromPlainText($plainText);
        }

        return $this->retrieve($authenticatable, $plainText);
    }

    /**
     * Determine if an access token exists and is valid.
     *
     * @param  AuthenticatableContract $authenticatable The authenticatable who owns the token.
     * @param  string|TokenInterface   $encryptedText   The encrypted token of the authenticatable.
     *
     * @return bool
     */
    public function check(AuthenticatableContract $authenticatable, $encryptedText)
    {
        return (bool) $this->retrieve($authenticatable, $encryptedText);
    }

    /**
     * Determine if an access token exists and is valid.
     *
     * @param  AuthenticatableContract $authenticatable The authenticatable who owns the token.
     * @param  string                  $plainText       The plain token of the authenticatable.
     *
     * @return bool
     */
    public function checkUsingPlainText(AuthenticatableContract $authenticatable, $plainText)
    {
        $token = $this->makeTokenFromPlainText($plainText);

        return $this->check($authenticatable, $token);
    }

    /**
     * Determine if an access token record exists and prolong the expire date if so.
     * If no prolong time given, we will reset the original expire time.
     *
     * @param  AuthenticatableContract $authenticatable The authenticatable who owns the token.
     * @param  string|TokenInterface   $encryptedText   The token of the authenticatable.
     * @param  int|null                $prolong         The prolong time in minutes.
     *
     * @return bool|AccessTokenInterface
     */
    public function checkAndProlong(AuthenticatableContract $authenticatable, $encryptedText, $prolong = null)
    {
        if (! $accessToken = $this->retrieve($authenticatable, $encryptedText)) {
            return false;
        }

        return $this->prolongAndUpdateAccessToken($accessToken, $prolong);
    }

    /**
     * Determine if an access token record exists and prolong the expire date if so.
     * If no prolong time given, we will reset the original expire time.
     *
     * @param  AuthenticatableContract $authenticatable The authenticatable who owns the token.
     * @param  string|TokenInterface   $plainText       The token of the authenticatable.
     * @param  int|null                $prolong         The prolong time in minutes.
     *
     * @return bool|AccessTokenInterface
     */
    public function checkUsingPlainTextAndProlong(AuthenticatableContract $authenticatable, $plainText, $prolong = null)
    {
        $token = $this->makeTokenFromPlainText($plainText);

        return $this->checkAndProlong($authenticatable, $token, $prolong);
    }

    /**
     * Generate a new access token in the storage and get the token.
     *
     * @param  AuthenticatableContract $authenticatable The authenticatable who owns the token.
     * @param  Carbon|null             $expiresAt       The optional expire date of the access token.
     *
     * @return AccessTokenInterface
     */
    public function generate(AuthenticatableContract $authenticatable, Carbon $expiresAt = null)
    {
        $token = $this->tokenGenerator->generate();
        $authenticatableId = $authenticatable->getAuthIdentifier();
        $expiresAt = $expiresAt ? (string) $expiresAt : null;

        $payload = (array) $this->repository->store($authenticatableId, (string) $token, $expiresAt);

        return $this->makeAccessToken($token, $payload);
    }

    /**
     * Update an access token in the storage.
     *
     * @param  AccessTokenInterface $accessToken The access token to be updated.
     *
     * @return bool
     */
    public function update(AccessTokenInterface $accessToken)
    {
        $token = (string) $accessToken;
        $expiresAt = (string) $accessToken->expiresAt();
        $authenticatableId = $accessToken->authenticatableId();

        return $this->repository->update($authenticatableId, $token, $expiresAt);
    }

    /**
     * Revive an token from the given plain text.
     *
     * @param  string $plainText The plain text to be converted back to token instance.
     *
     * @return TokenInterface
     */
    public function makeTokenFromPlainText($plainText)
    {
        return $this->tokenGenerator->fromPlain($plainText);
    }

    /**
     * Revive an token from the given plain text.
     *
     * @param  string $encryptedText The encrypted token to be converted back to token instance.
     *
     * @return TokenInterface
     */
    public function makeTokenFromEncryptedText($encryptedText)
    {
        return $this->tokenGenerator->fromEncrypted($encryptedText);
    }

    /**
     * Retrieve the first resource by the given attributes.
     *
     * @param  array $queryParams The key - value pairs to match.
     * @param  array $attributes  The attributes to be returned from the storage.
     *
     * @return AccessTokenInterface|null
     */
    public function retrieveByAttributes(array $queryParams, array $attributes = ['*'])
    {
        if (! $attributes = $this->repository->retrieveByAttributes($queryParams, $attributes)) {
            return;
        }

        return $attributes ? $this->makeAccessToken((array) $attributes) : null;
    }

    /**
     * Delete the given access token from the storage.
     *
     * @param  AccessTokenInterface|string $accessToken The access token or the encrypted text to be deleted.
     *
     * @return bool
     */
    public function delete($accessToken)
    {
        return (bool) $this->repository->delete($accessToken->authenticatableId(), (string) $accessToken);
    }

    /**
     * Delete the expired access tokens from the storage.
     *
     * @return void
     */
    public function deleteExpired()
    {
        $this->repository->deleteExpired();
    }

    /**
     * Retrieve an access token from the storage.
     *
     * @param  int    $authenticatableId
     * @param  string $encryptedText
     *
     * @return GenericAccessToken|null
     */
    private function retrieveFromRepository($authenticatableId, $encryptedText)
    {
        if (! $attributes = $this->repository->retrieve($authenticatableId, $encryptedText)) {
            return;
        }

        return $this->makeAccessToken((array) $attributes);
    }

    /**
     * Prolong the access token then update it in the storage.
     *
     * @param  AccessTokenInterface $accessToken
     * @param  int|null             $prolong
     *
     * @return bool|AccessTokenInterface
     */
    private function prolongAndUpdateAccessToken(AccessTokenInterface $accessToken, $prolong = null)
    {
        $accessToken = $this->prolongAccessToken($accessToken, $prolong);

        if ($this->update($accessToken)) {
            return $accessToken;
        }

        return false;
    }

    /**
     * Prolong an access token.
     *
     * @param AccessTokenInterface $accessToken
     * @param int|null             $prolong
     *
     * @return AccessTokenInterface
     */
    private function prolongAccessToken(AccessTokenInterface $accessToken, $prolong = null)
    {
        $prolong = $prolong ? $prolong * 60 : $this->getNow()->diffInSeconds($accessToken->createdAt());

        return $accessToken->prolong($prolong);
    }

    /**
     * Get a new access token instance with the given attributes.
     *
     * @param array|TokenInterface $token
     * @param array                $attributes
     *
     * @return GenericAccessToken
     */
    private function makeAccessToken($token, array $attributes = [])
    {
        if (! $token instanceof TokenInterface) {
            $attributes = $token;

            $token = $this->makeTokenFromEncryptedText(array_pull($attributes, 'token'));
        }

        return new GenericAccessToken($token, $attributes);
    }

    /**
     * Get the current UNIX timestamp.
     *
     * @return Carbon
     */
    private function getNow()
    {
        return Carbon::now();
    }
}
