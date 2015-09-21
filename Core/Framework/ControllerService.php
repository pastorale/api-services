<?php
namespace AppBundle\Services\Core\Framework;

use AppBundle\Traits\ContainerConstructorTrait;
use FOS\RestBundle\Controller\FOSRestController;

class ControllerService extends FOSRestController
{
//    use ContainerConstructorTrait;

    public function optionsAction()
    {
    
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function handleFetch($object, $msg = "Resource cannot be found")
    {
        if (isset($object)) {
            return $this->handleView($this->view($object, 200));
        } else {
            return $this->returnMessage($msg, 404);
        }
    }

    public function returnMessage($msg, $status)
    {
        $errorMsg = ['code' => $status, 'message' => $msg];
        return $this->handleView($this->view($errorMsg, $status));
    }

}