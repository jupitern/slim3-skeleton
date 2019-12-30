<?php

namespace App\Business;

use App\Enum\HttpStatusCode;
use App\Messages\Message;
use App\Repository\OAuthAccessTokenRepository;
use League\OAuth2\Server\AuthorizationValidators\BearerTokenValidator;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Exception\OAuthServerException;

/**
 * Class AuthenticationBusiness
 * @package App\Business
 * @author Jerfeson Guerreiro <jerfeson_guerreiro@hotmail.com>
 */
class AuthenticationBusiness extends Business
{
    /**
     * @throws \Exception
     */
    public function login()
    {
        try {
            $this->validator();
            return $this->getResponse()->withJson([
                'result' => Message::STATUS_SUCCESS,
                'message' => Message::LOGIN_SUCCESSFUL,
                'redirect_url' => '',
            ])->withStatus(HttpStatusCode::OK);
        } catch (OAuthServerException $exception) {
            return $this->getResponse()->withJson([
                'result' => Message::STATUS_ERROR,
                'message' => Message::ACCESS_DENIED
            ])->withStatus(HttpStatusCode::UNAUTHORIZED);
        }
    }

    /**
     *
     */
    private function validator()
    {
        $this->validateBearer();
    }

    /**
     *  Check if Bearer token is ok, check expiration date and token is revoked
     *  Update the request with data about the user
     * @return mixed
     * @throws OAuthServerException
     */
    private function validateBearer()
    {
        $oauth2Config = app()->getConfig('settings.oauth2');
        $accessTokenRepository = new OAuthAccessTokenRepository();
        $validator = new BearerTokenValidator($accessTokenRepository);
        $cryptKey = new CryptKey(file_get_contents($oauth2Config['public_key']));
        $validator->setPublicKey($cryptKey);
        $this->setRequest($validator->validateAuthorization($this->getRequest()));
    }
}