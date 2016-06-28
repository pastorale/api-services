<?php
namespace AppBundle\Services\Core\Framework\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use AppBundle\Entity\Core\User\User;

class UserListener
{
    private $container;
    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof User) {
            return;
        }
        $entityManager = $args->getEntityManager();
        $entity->setUsername($entity->getEmail());
        $entity->setUsernameCanonical($entity->getEmail());
        $entity->setEmailCanonical($entity->getEmail());
        $entity->setCode(uniqid());
        $entityManager->persist($entity);
        $entityManager->flush();
    }
}