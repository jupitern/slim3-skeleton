<?php


namespace App\Middleware;

use App\Enum\HttpStatusCode;
use App\Messages\Message;
use App\Repository\OAuthAccessTokenRepository;
use League\OAuth2\Server\AuthorizationValidators\BearerTokenValidator;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class OAuthAuthenticationToken
 * @package App\Middleware
 * @author Jerfeson Guerreiro <jerfeson_guerreiro@hotmail.com>

 */
class OAuthAuthenticationToken
{
    /**
     * @var array
     */
    private $noOAuthRoutes = [
        '/',
        'authentication/authentication/token',
        'authentication/authentication/login'

    ];
    /**
     * @var
     */
    private $getAuthenticationBusiness;

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param $next
     * @return mixed
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
    {
        $route = $request->getAttribute('route');
        if ($route && count($route->getArguments())) {
            $routeAccessed = $route->getArguments()['module'] . "/" . $route->getArguments()['class'] . "/" . $route->getArguments()['method'];

            #Verify if route can be accessed without token
            if (in_array($routeAccessed, $this->noOAuthRoutes)) {
                return $next($request, $response);
            }

            try {
                //Your Token Validation Business Rule
                $this->validator($request);
                return $next($request, $response);
            } catch (OAuthServerException $exception) {
                return  $response->withJson([
                    'result' => Message::STATUS_ERROR,
                    'message' => Message::ACCESS_DENIED
                ])->withStatus(HttpStatusCode::UNAUTHORIZED);
            }

        }

        return $next($request, $response);
    }

    private function validator($request)
    {
        $this->validateBearer($request);
    }


    /**
     *  Check if Bearer token is ok, check expiration date and token is revoked
     *  Update the request with data about the user
     * @return mixed
     * @throws OAuthServerException
     */
    private function validateBearer($request)
    {
        $oauth2Config = app()->getConfig('settings.oauth2');
        $accessTokenRepository = new OAuthAccessTokenRepository();
        $validator = new BearerTokenValidator($accessTokenRepository);
        $cryptKey = new CryptKey(file_get_contents($oauth2Config['public_key']));
        $validator->setPublicKey($cryptKey);
        $validator->validateAuthorization($request);
    }
}
