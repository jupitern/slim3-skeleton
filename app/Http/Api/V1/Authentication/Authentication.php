<?php


namespace App\Http\Api\V1\Authentication;


use App\Business\AuthenticationBusiness;
use App\Business\UserBusiness;
use App\Enum\HttpStatusCode;
use App\Http\Controller;

/**
 * Class Authentication
 * @package App\Http\Api\V1\Authentication
 * @author Jerfeson Guerreiro <jerfeson_guerreiro@hotmail.com>
 */
class Authentication extends Controller
{
    /**
     * @var AuthenticationBusiness
     */
    private $authenticationBusiness;
    /**
     * @var UserBusiness
     */
    private $userBusiness;

    public function loginAction()
    {
        try {
            $this->getAuthenticationBusiness()->setRequest($this->request);
            return $this->getAuthenticationBusiness()->login();
        } catch (\Exception $e) {
            return $this->response->withJson(
                json_decode($e->getMessage()))->withStatus($e->getCode());
        }
    }

    /**
     *
     */
    public function tokenAction()
    {
        try {
            $this->oAuthServer->respondToAccessTokenRequest($this->request, $this->response);
            return $this->response->withStatus(HttpStatusCode::OK);
        } catch (\Exception $e) {
            return $this->response->withStatus(HttpStatusCode::UNAUTHORIZED);
        }
    }


    /**
     * @return UserBusiness
     */
    protected function getUserBusiness()
    {
        if (! $this->userBusiness) {
            $this->userBusiness  = new UserBusiness($this->request, $this->response);
        }

        return $this->userBusiness;
    }
    
    /**
     * @return AuthenticationBusiness
     */
    private function getAuthenticationBusiness()
    {
        if (! $this->authenticationBusiness) {
            $this->authenticationBusiness  = new AuthenticationBusiness($this->request, $this->response);
        }

        return $this->authenticationBusiness;
    }
}