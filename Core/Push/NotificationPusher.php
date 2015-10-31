<?php

namespace AppBundle\Services\Core\Push;

use AppBundle\Entity\Core\Core\Push;
use AppBundle\Entity\Organisation\Organisation;
use AppBundle\Services\Core\Framework\BaseController;
use AppBundle\Entity\Core\Message\Message;

class NotificationPusher extends BaseController {

    public function push(Organisation $organisation, Message $message, Push $push) {
        //get paging
        $current = $push->getCurrent();
        $size = $push->getSize();
        $total = $push->getTotal();
        if ($current < $total) {
            //get possions
            $em = $this->getContainer()->get('doctrine')->getManager();
            $queryBuilder = $em->createQueryBuilder()
                    ->select('position')
                    ->from('AppBundle:Organisation\Position', 'position')
                    ->join('position.employee', 'employee')
                    ->join('position.employer', 'employer', 'WITH', 'employer = ?1')->setParameter(1, $organisation)
                    ->andWhere('position.active = ?2')->setParameter(2, true)
                    ->andWhere('position.createdAt <= ?3')->setParameter(3, $message->getCreatedAt())
                    ->setFirstResult($current * $size)
                    ->setMaxResults($size);
            $possions = $queryBuilder->getQuery()->getResult();
            //set message
            $tag = $this->getContainer()->get('doctrine')
                    ->getRepository('AppBundle:Core\Core\Tag')
                    ->findByName(Message::TAG_NOTIFICATION);
            foreach ($possions as $possion) {
                $message = new Message();
                $message->setRecipient($possion->getEmployee());
                if ($tag) {
                    $message->addTag($tag);
                }
                $em->persist($message);
                $em->flush();
            }
            //update push
            $push->setCurrent($current + 1);
            $em->persist($push);
            $em->flush();
        }
    }

}
