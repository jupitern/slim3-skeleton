<?php

namespace App\ServiceProviders;

use App\Model\OAuthRefreshTokenCodeModel;
use App\Repository\OAuthAccessTokenRepository;
use App\Repository\OAuthAuthCodeRepository;
use App\Repository\OAuthClientRepository;
use App\Repository\OAuthRefreshTokenRepository;
use App\Repository\OAuthScopeRepository;
use App\Repository\UserRepository;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;

/**
 * Class OauthServer
 * @package App\ServiceProviders
 * @author Jerfeson Guerreiro <jerfeson_guerreiro@hotmail.com>
 */
class OAuthServer implements ProviderInterface
{

    /**
     *
     */
    public static function register()
    {
        app()->getContainer()[AuthorizationServer::class] = function ($c) {

            $oauth2Config = app()->getConfig('settings.oauth2');
            $clienteRepository = new OAuthClientRepository();
            $tokenRepository = new OAuthAccessTokenRepository();
            $scopeRepository = new OAuthScopeRepository();
            $authCodeRepository = new OAuthAuthCodeRepository();
            $refreshTokenRepository = new OAuthRefreshTokenRepository();
            $userRepository = new UserRepository();

            $authCodeGrand = new AuthCodeGrant($authCodeRepository, $refreshTokenRepository, new \DateInterval('P1M'));

            $passWord = new \League\OAuth2\Server\Grant\PasswordGrant(
                $userRepository,
                $refreshTokenRepository
            );
            $passWord->setRefreshTokenTTL(new \DateInterval('P1M')); // refresh tokens will expire after 1 month

            /** @var AuthorizationServer $oauthServer */
            $oauthServer = new AuthorizationServer(
                $clienteRepository,
                $tokenRepository,
                $scopeRepository,
                file_get_contents($oauth2Config['private_key']),
                file_get_contents($oauth2Config['public_key'])
            );

            // Enable the password grant on the server
            $oauthServer->enableGrantType(
                $passWord,
                new \DateInterval('PT1H') // access tokens will expire after 1 hour
            );
            return $oauthServer;
        };
    }
}
