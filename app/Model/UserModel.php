<?php

namespace App\Model;

use League\OAuth2\Server\Entities\UserEntityInterface;

/**
 * Class User
 * @package App\Model
 * @author Jerfeson Guerreiro <jerfeson@codeis.com.br>
 * @version 3.0.0
 * @since   3.0.0
 */
class UserModel extends Model implements UserEntityInterface
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    protected $table = 'user';
    protected $fillable = ['name', 'user', 'password'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function client()
    {
        return $this->hasOne(ClientModel::class, 'id', 'client_id');
    }
}
