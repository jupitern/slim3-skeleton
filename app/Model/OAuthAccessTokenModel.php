<?php

namespace App\Model;

use DateTime;
use Lcobucci\JWT\Token;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;

/**
 * Class OAuthAccessTokenModel
 * @package App\Model
 * @author Jerfeson Guerreiro <jerfeson_guerreiro@hotmail.com>
 */
class OAuthAccessTokenModel extends Model implements AccessTokenEntityInterface
{
    protected $table = 'oauth_access_token';

    /**
     * @var OAuthClientModel
     */
    protected $client;

    /**
     * @var UserModel
     */
    protected $userIdentifier;

    /**
     * @var
     */
    private $expiryDateTime;

    /**
     * @return OAuthClientModel
     */
    public function getClient(): OAuthClientModel
    {
        return $this->client;
    }

    /**
     * @param OAuthClientModel $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * @return UserModel
     */
    public function getUserIdentifier(): UserModel
    {
        return $this->userIdentifier;
    }

    /**
     * @param UserModel $userIdentifier
     */
    public function setUserIdentifier($userIdentifier)
    {
        $this->userIdentifier = $userIdentifier;
    }

    /**
     * @param DateTime $dateTime
     */
    public function setExpiryDateTime(DateTime $dateTime)
    {
        $this->expiryDateTime = $dateTime;
    }

    /**
     * Generate a JWT from the access token
     *
     * @param CryptKey $privateKey
     *
     * @return Token
     */
    public function convertToJWT(CryptKey $privateKey)
    {
        return (new Builder())
            ->setAudience($this->getClient()->getIdentifier())
            ->setId($this->getIdentifier())
            ->setIssuedAt(time())
            ->setNotBefore(time())
            ->setExpiration($this->getExpiryDateTime()->getTimestamp())
            ->setSubject($this->getUserIdentifier()->id)
            ->set('scopes', $this->getScopes())
            ->sign(new Sha256(), new Key($privateKey->getKeyPath(), $privateKey->getPassPhrase()))
            ->getToken();
    }

    /**
     * Get the token's expiry date time.
     *
     * @return DateTime
     */
    public function getExpiryDateTime()
    {
        return $this->expiryDateTime;
    }

    /**
     * Associate a scope with the token.
     *
     * @param ScopeEntityInterface $scope
     */
    public function addScope(ScopeEntityInterface $scope)
    {
        // TODO: Implement addScope() method.
    }

    /**
     * Return an array of scopes associated with the token.
     *
     * @return ScopeEntityInterface[]
     */
    public function getScopes()
    {
        // TODO: Implement getScopes() method.
    }
}
