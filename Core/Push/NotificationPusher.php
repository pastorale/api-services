<?php
namespace AppBundle\Services\Core\Message;

use AppBundle\Entity\Core\Core\Push;
use AppBundle\Entity\Organisation\Organisation;
use AppBundle\Services\Core\Framework\BaseController;
use AppBundle\Entity\Core\Message\Message;

class NotificationPusher extends BaseController
{
    public function push(Organisation $organisation, Message $message, Push $push)
    {

    }
}