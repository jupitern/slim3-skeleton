<?php

namespace App\Model;

/**
 * Class ClientModel
 * @package App\Model
 * @author Jerfeson Guerreiro <jerfeson_guerreiro@hotmail.com>

 */
class ClientModel extends Model
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    protected $table = 'client';
    protected $fillable = ['name', 'status'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function client()
    {
        return $this->hasOne(OAuthClientModel::class, 'id', 'oauth_client_id');
    }
}
