<?php
// src/Solutions/AppBundle/Services/User/UserManager.php

namespace AppBundle\Services\User;

use AppBundle\Services\Traits\ContainerConstructorTrait;

class UserManager
{
    use ContainerConstructorTrait;
    private $container;
    /**
     * @param array $params
     * @return \AppBundle\Entity\User\UserAccount
     */
    public function findOne(array $params)
    {
        return $this->_loadUser($params['username']);
    }

    /**
     * @param $username
     * @return \AppBundle\Entity\User\UserAccount
     */
    private function _loadUser($username)
    {
        $container = $this->container;
        $userManager = $container->get('fos_user.user_manager');
        if (strpos($username, '@') > 0) {
            $user = $userManager->findUserByEmail($username);
        } else {
            $user = $userManager->findUserByUsername($username);
        }
        return $user;
    }
}