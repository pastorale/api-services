<?php
// src/Solutions/AppBundle/Services/Core/User/UserRetriever.php

namespace AppBundle\Services\Core\User;

use AppBundle\Entity\Core\User\User;
use AppBundle\Services\Core\Framework\BaseController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class UserRetriever extends BaseController
{
    const USERNAME_SEPARATOR = ' endofusername ';

    public function encodeUserKey($mode, $username, $password)
    {
        return '{' . $mode . '}:' . $username . $this::USERNAME_SEPARATOR . $password;
    }

    public function decodeUserKey($str)
    {
        $mode = substr($str, 1, strpos($str, '}:') - 1);
        $startPos = strlen('{' . $mode . '}:');
        $username = substr($str, $startPos, strpos($str, $this::USERNAME_SEPARATOR) - $startPos);
        $password = substr($str, strlen('{' . $mode . '}:' . $username . $this::USERNAME_SEPARATOR));
        return ['mode' => $mode, 'username' => $username, 'password' => $password];
    }

    public function getLoggedinUser()
    {
        $container = $this->container;
// check if the logged in is same as needle;
        $cache_ns = User::CACHE_NS;
        $cache = $this->container->get('memory_cache');
        $cachedUser = $cache->getValue($cache_ns, $container->get('security.token_storage')->getToken()->getUsername());
        if (empty($cachedUser)) {
            throw new Exception('user was not cached properly'); // properly not yet logged in ???
        }

        $uid = $cachedUser['id'];
        $user = $container->get('doctrine')->getRepository('AppBundle\Entity\Core\User\User')->find($uid);
        return $user;
    }

    public function find($id)
    {
        if (is_numeric($id)) {
            return $this->getDoctrine()->getRepository('AppBundle:Core\User\User')->find($id);
        } else {
            return $this->findOneByUsernameEmail($id);
        }
    }

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