<?php


namespace App\Business;

use App\Model\UserModel;
use App\Repository\UserRepository;

/**
 * Class UserBusiness
 * @package App\Business
 * @author Jerfeson Guerreiro <jerfeson_guerreiro@hotmail.com>
 */
class UserBusiness extends Business
{
    protected $repository = UserRepository::class;

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getUserByUserCredentials()
    {
        $user =  $this->getRepository()->getUser(
            $this->request->getParsedBody()['username'],
            $this->request->getParsedBody()['password']
        );

        $this->validate($user);
        return $user;
    }

    /**
     * @param $user
     * @throws \Exception
     */
    private function validate($user)
    {
        if ($user->status == UserModel::STATUS_INACTIVE) {
            throw new \Exception("error");
        }
    }
}
