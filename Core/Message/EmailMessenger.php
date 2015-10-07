<?php
namespace AppBundle\Services\Core\Message;

use AppBundle\Entity\Core\Message\Message;
use AppBundle\Entity\Core\Message\MessageSetting;
use AppBundle\Services\Core\Framework\BaseController;

class EmailMessenger extends BaseController
{
    public function prepareMessageContent(Message $message, MessageSetting $setting, array $vars)
    {
        $templates = array('msg-' . $setting->getId() => $setting->getTemplate());
        $env = new \Twig_Environment(new \Twig_Loader_Array($templates));
        $message->setContent($env->render('hello', $vars));
        return $message;
    }

    public function sendMessage(Message $message)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($message->getSubject())
            ->setFrom($message->getSender())
            ->setTo($message->getRecipient())
            ->setContentType("text/html")
            ->setBody($message->getContent());

        $mailer = $this->mailer;
        if (!$mailer->send($message)) {

        }
        $spool = $mailer->getTransport()->getSpool();
        $transport = $this->container->get('swiftmailer.transport.real');
        $spool->flushQueue($transport);
    }
}