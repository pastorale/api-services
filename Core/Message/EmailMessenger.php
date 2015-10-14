<?php
namespace AppBundle\Services\Core\Message;

use AppBundle\Entity\Core\Message\Message;
use AppBundle\Entity\Core\Message\MessageSetting;
use AppBundle\Services\Core\Framework\BaseController;

class EmailMessenger extends BaseController
{
    public function prepareMessageContent(Message $message, MessageSetting $setting, array $vars)
    {
        $keyBody = 'msg-body-' . $setting->getId();
        $keySubject = 'msg-subject-' . $setting->getId();
        $templates = array($keySubject => $setting->getSubjectTemplate(), $keyBody => $setting->getBodyTemplate());
        $env = new \Twig_Environment(new \Twig_Loader_Array($templates));
        $message->setSubject($env->render($keySubject, $vars));
        $message->setBody($env->render($keyBody, $vars));
        return $message;
    }

    public function sendMessage(Message $message)
    {
        $email = \Swift_Message::newInstance()
            ->setSubject($message->getSubject())
            ->setFrom($message->getSender())
            ->setTo($message->getRecipient())
            ->setContentType("text/html")
            ->setBody($message->getBody());

        $mailer = $this->get('mailer');
        if (!$mailer->send($email)) {

        }
        $spool = $mailer->getTransport()->getSpool();
        $transport = $this->container->get('swiftmailer.transport.real');
        $spool->flushQueue($transport);
    }
}