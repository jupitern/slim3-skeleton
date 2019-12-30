<?php


namespace App\Repository;

use App\Helpers\Password;
use App\Messages\Message;
use App\Model\UserModel;
use Illuminate\Database\Query\Builder;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;


class UserRepository extends Repository implements UserRepositoryInterface
{
    protected $model = UserModel::class;

    /**
     * Get a user entity.
     *
     * @param string $username
     * @param string $password
     * @param string $grantType The grant type used
     * @param ClientEntityInterface $clientEntity
     *
     * @return UserEntityInterface
     * @throws \Exception
     */
    public function getUserEntityByUserCredentials($username, $password, $grantType, ClientEntityInterface $clientEntity)
    {
        /** @var Builder $query */
        $user = $this->getUser($username, $password);
        $user->setIdentifier($user);
        return $user;
    }

    /**
     * Get a user entity.
     *
     * @param $username
     * @param $password
     * @return UserEntityInterface
     * @throws \Exception
     */
    public function getUser($username, $password)
    {
        /** @var Builder $query */
        $query = $this->newQuery();
        $query->where('username', '=', $username);

        $user = $this->doQuery($query, false, false)->first();
        if (! $user->password && ! Password::verify($password, $user->password)) {
            throw new \Exception(Message::ACCESS_DENIED);
        }
        return $user;
    }
}
