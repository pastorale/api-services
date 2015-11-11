<?php

namespace AppBundle\Services\Core\Push;

use AppBundle\Entity\Core\Core\Push;
use AppBundle\Entity\Organisation\Organisation;
use AppBundle\Services\Core\Framework\BaseController;
use AppBundle\Entity\Core\Message\Message;

class NotificationPusher extends BaseController
{

    public function push(Organisation $organisation, Message $message, Push $push, $current)
    {
        //get paging
        $newCurrent = $push->getCurrent();
        $size = $push->getSize();
        $total = $push->getTotal();
        if ($newCurrent <= $total && $newCurrent > $current) {
            //get positions
            $em = $this->getContainer()->get('doctrine')->getManager();
            $queryBuilder = $em->createQueryBuilder()
                ->select('position')
                ->from('AppBundle:Organisation\Position', 'position')
                ->join('position.employee', 'employee')
                ->join('position.employer', 'employer', 'WITH', 'employer = ?1')->setParameter(1, $organisation)
                ->andWhere('position.active = ?2')->setParameter(2, true)
                ->andWhere('position.createdAt <= ?3')->setParameter(3, $message->getCreatedAt())
                ->setFirstResult($current * $size)
                ->setMaxResults(($newCurrent - $current) * $size);
            $positions = $queryBuilder->getQuery()->getResult();
            //set message
            $tag = $this->getDoctrine()
                ->getRepository('AppBundle:Core\Classification\Tag')
                ->findOneBy(array('name' => Message::TAG_NOTIFICATION));
            foreach ($positions as $position) {
                $messageUser = new Message();
                $messageUser->setRecipient($position->getEmployee());
                $messageUser->setSubject($message->getSubject());
                $messageUser->setBody($message->getBody());
                $messageUser->setParent($message);
                if ($tag) {
                    $messageUser->addTag($tag);
                }
                $em->persist($messageUser);
            }
            $em->flush();
            //update push
            $push->setCurrent($newCurrent);
            $em->persist($push);
            $em->flush();
            return true;
        }
        return false;
    }

}
