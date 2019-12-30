<?php

namespace App\Model;

use League\OAuth2\Server\Entities\ClientEntityInterface;

/**
 * Class OAuthClientModel
 * @package App\Model
 * @author Jerfeson Guerreiro <jerfeson_guerreiro@hotmail.com>

 */
class OAuthClientModel extends Model implements ClientEntityInterface
{
    protected $table = 'oauth_client';
    protected $fillable = ['secret'];

    /**
     * Get the client's name.
     *
     * @return string
     */
    public function getName()
    {
        // TODO: Implement getName() method.
    }

    /**
     * Returns the registered redirect URI (as a string).
     *
     * Alternatively return an indexed array of redirect URIs.
     *
     * @return string|string[]
     */
    public function getRedirectUri()
    {
        // TODO: Implement getRedirectUri() method.
    }
}
