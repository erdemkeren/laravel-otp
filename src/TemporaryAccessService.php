<?php

namespace Erdemkeren\TemporaryAccess;

use Carbon\Carbon;
use Erdemkeren\TemporaryAccess\Contracts\AccessCodeInterface;
use Erdemkeren\TemporaryAccess\Contracts\AccessTokenInterface;
use Erdemkeren\TemporaryAccess\Contracts\TokenInformationInterface;
use Erdemkeren\TemporaryAccess\Contracts\AccessCodeGeneratorInterface;
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
     * The access code generator implementation.
     *
     * @var AccessCodeGeneratorInterface
     */
    private $codeGenerator;

    /**
     * TemporaryAccessService constructor.
     *
     * @param AccessTokenRepositoryInterface $repository
     * @param AccessCodeGeneratorInterface   $codeGenerator
     */
    public function __construct(AccessTokenRepositoryInterface $repository, AccessCodeGeneratorInterface $codeGenerator)
    {
        $this->repository = $repository;
        $this->codeGenerator = $codeGenerator;
    }

    /**
     * Retrieve an access token from the storage by the plain code.
     *
     * @param AuthenticatableContract          $authenticatable The authenticatable who owns the access code.
     * @param string|TokenInformationInterface $plainText       The access code of the authenticatable.
     *
     * @return null|AccessTokenInterface
     */
    public function retrieveByCode(AuthenticatableContract $authenticatable, $plainText)
    {
        if (! $plainText instanceof TokenInformationInterface) {
            $plainText = $this->makeAccessCode($plainText);
        }

        $authenticatableIdentifier = $authenticatable->getAuthIdentifier();

        return $this->retrieveFromRepository($authenticatableIdentifier, $plainText->encrypted());
    }

    /**
     * Retrieve an access token from the storage by the actual token.
     *
     * @param AuthenticatableContract          $authenticatable The authenticatable who owns the access code.
     * @param string|TokenInformationInterface $encryptedText   The access code of the authenticatable.
     *
     * @return null|AccessTokenInterface
     */
    public function retrieveByToken(AuthenticatableContract $authenticatable, $encryptedText)
    {
        if ($encryptedText instanceof TokenInformationInterface) {
            $encryptedText = $encryptedText->encrypted();
        }

        $authenticatableIdentifier = $authenticatable->getAuthIdentifier();

        return $this->retrieveFromRepository($authenticatableIdentifier, $encryptedText);
    }

    /**
     * Determine if an access code exists and is valid.
     *
     * @param  AuthenticatableContract          $authenticatable The authenticatable who owns the access code.
     * @param  string|TokenInformationInterface $plainText       The access token of the authenticatable.
     *
     * @return bool
     */
    public function checkCode(AuthenticatableContract $authenticatable, $plainText)
    {
        return (bool) $this->retrieveByCode($authenticatable, $plainText);
    }

    /**
     * Determine if an access token exists and is valid.
     *
     * @param  AuthenticatableContract          $authenticatable The authenticatable who owns the access code.
     * @param  string|TokenInformationInterface $encryptedText   The encrypted access token of the authenticatable.
     *
     * @return bool
     */
    public function checkToken(AuthenticatableContract $authenticatable, $encryptedText)
    {
        return (bool) $this->retrieveByToken($authenticatable, $encryptedText);
    }

    /**
     * Determine if an access code record exists and prolong the expire date if so.
     * If no prolong time given, we will reset the original expire time.
     *
     * @param  AuthenticatableContract          $authenticatable The authenticatable who owns the access code.
     * @param  string|TokenInformationInterface $plainText       The access code of the authenticatable.
     * @param  int|null                         $prolong         The prolong time in minutes.
     *
     * @return bool|AccessTokenInterface
     */
    public function checkCodeAndProlong(AuthenticatableContract $authenticatable, $plainText, $prolong = null)
    {
        if (! $accessToken = $this->retrieveByCode($authenticatable, $plainText)) {
            return false;
        }

        return $this->prolongAndUpdateAccessToken($accessToken, $prolong);
    }

    /**
     * Determine if an access token record exists and prolong the expire date if so.
     * If no prolong time given, we will reset the original expire time.
     *
     * @param  AuthenticatableContract          $authenticatable The authenticatable who owns the access code.
     * @param  string|TokenInformationInterface $encryptedText   The access code of the authenticatable.
     * @param  int|null                         $prolong         The prolong time in minutes.
     *
     * @return bool|AccessTokenInterface
     */
    public function checkTokenAndProlong(AuthenticatableContract $authenticatable, $encryptedText, $prolong = null)
    {
        if (! $accessToken = $this->retrieveByToken($authenticatable, $encryptedText)) {
            return false;
        }

        return $this->prolongAndUpdateAccessToken($accessToken, $prolong);
    }

    /**
     * Generate a new access token in the storage and get the access code.
     *
     * @param  AuthenticatableContract $authenticatable The authenticatable who owns the access code.
     * @param  Carbon|null             $expiresAt       The optional expire date of the access token.
     *
     * @return AccessTokenInterface
     */
    public function generate(AuthenticatableContract $authenticatable, Carbon $expiresAt = null)
    {
        $accessCode = $this->codeGenerator->generate();
        $authenticatableId = $authenticatable->getAuthIdentifier();
        $expiresAt = $expiresAt ? (string) $expiresAt : null;

        $payload = $this->repository->store($authenticatableId, (string) $accessCode, $expiresAt);
        $payload['plain'] = $accessCode->plain();

        return $this->makeAccessToken($payload);
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
        $token = $accessToken->token();
        $expiresAt = $accessToken->expiresAt();
        $authenticatableId = $accessToken->authenticatableId();

        return $this->repository->update($authenticatableId, $token, (string) $expiresAt);
    }

    /**
     * Revive an access code from the given plain text.
     *
     * @param  string $plainText The plain text code to be converted back to access code instance.
     *
     * @return AccessCodeInterface
     */
    public function makeAccessCode($plainText)
    {
        return $this->codeGenerator->fromPlain($plainText);
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
        $attributes = $this->repository->retrieveByAttributes($queryParams, $attributes);

        return $attributes ? $this->makeAccessToken((array) $attributes) : null;
    }

    /**
     * Delete the given access token from the storage.
     *
     * @param  AccessTokenInterface $accessToken The access token to be deleted.
     *
     * @return bool
     */
    public function delete(AccessTokenInterface $accessToken)
    {
        return (bool) $this->repository->delete($accessToken->authenticatableId(), $accessToken->token());
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
            return null;
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
     * @param array $attributes
     *
     * @return GenericAccessToken
     */
    private function makeAccessToken(array $attributes)
    {
        return new GenericAccessToken($attributes);
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
