<?php
// src/Solutions/AppBundle/Services/Core/User/UserRetriever.php

namespace AppBundle\Services\Core\User;

use AppBundle\Services\Core\Framework\ControllerService;
use AppBundle\Traits\ContainerConstructorTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class UserRetriever extends ControllerService
{

    /**
     * @param string $needle
     * @return \AppBundle\Entity\Core\User\User
     */
    public function findOneByUsernameEmail($needle)
    {
        $container = $this->container;
        $userManager = $container->get('fos_user.user_manager');
        if (strpos($needle, '@') > 0) {
            $user = $userManager->findUserByEmail($needle);
        } else {
            $user = $userManager->findUserByUsername($needle);
        }
        return $user;
    }



}