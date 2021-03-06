<?php
namespace AppBundle\Services\Core\Message;

use AppBundle\Entity\Core\Message\Message;
use AppBundle\Entity\Core\Message\MessageTemplate;
use AppBundle\Entity\Organisation\Organisation;
use AppBundle\Services\Core\Framework\BaseController;

class EmailMessenger extends BaseController
{
    public function prepareMessageContent(Message $message, MessageTemplate $setting, array $vars)
    {
        $keyBody = 'msg-body-' . $setting->getId();
        $keySubject = 'msg-subject-' . $setting->getId();
        $templates = array($keySubject => $setting->getSubjectTemplate(), $keyBody => $setting->getBodyTemplate());
        $env = new \Twig_Environment(new \Twig_Loader_Array($templates));
        $message->setSubject($env->render($keySubject, $vars));
        $message->setBody($env->render($keyBody, $vars));
        return $message;
    }

    public function sendMessage(Message $message,$from=null,$to=null)
    {
        $from = $from === null ? $message->getSender()->getEmail():$from;
        $to = $to === null ? $message->getRecipient()->getEmail():$to;
        $email = \Swift_Message::newInstance()
            ->setSubject($message->getSubject())
            ->setFrom($from)
            ->setTo($to)
            ->setContentType("text/html")
            ->setBody($message->getBody());

        $mailer = $this->get('mailer');
        if (!$mailer->send($email)) {

        }
        $spool = $mailer->getTransport()->getSpool();
        $transport = $this->container->get('swiftmailer.transport.real');
        $spool->flushQueue($transport);
    }
    public function sendInformationLoginCloudbook(Organisation $organisation,$data){
        $type = $data['type'];
        $users =$data['users'];
        $emailTemplate = $this->getDoctrine()->getRepository('AppBundle:Core\Message\MessageTemplate')->findOneByCode($type);
        $userManager = $this->get('fos_user.user_manager');
        $generator = $this->get('app.core.core.generator');
        $plainPassword =$generator::generateRandomString(6);
        foreach ($users as $user){
            $userEntity = $userManager->findUserByUsername($user['web_username']);
            $userEntity->setPlainPassword($plainPassword);
            $userManager->updateUser($userEntity);

            $vars['COMPANY_NAME'] = $organisation->getName();
            $vars['FULL_NAME'] = $user['full_name'];
            $vars['WEB_USERNAME']=$user['web_username'];
            $vars['WEB_PASSWORD']=$plainPassword;
            $vars['APP_USERNAME']=$organisation->getCode();
            $vars['APP_PASSWORD']=$user['app_password'];

            $message = new Message();
            $this->prepareMessageContent($message,$emailTemplate,$vars);
            $this->sendMessage($message,'noreply@magentapulse.com',$user['email']);

        }

    }
}