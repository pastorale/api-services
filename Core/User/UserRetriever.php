<?php
// src/Solutions/AppBundle/Services/Core/User/UserRetriever.php

namespace AppBundle\Services\User;

use AppBundle\Services\Core\Framework\ControllerService;
use AppBundle\Traits\ContainerConstructorTrait;

class UserRetriever extends ControllerService
{
    use ContainerConstructorTrait;
    private $container;
    /**
     * @param array $params
     * @return \AppBundle\Entity\Core\User\User
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