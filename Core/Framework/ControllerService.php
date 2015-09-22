<?php
namespace AppBundle\Services\Core\Framework;

use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Response;

class ControllerService extends FOSRestController implements ClassResourceInterface
{
//    use ContainerConstructorTrait;

    public function optionsAction()
    {
        $response = new Response();
        $response->headers->set('Allow', 'OPTIONS, GET, PATCH, POST, PUT');
        return $response;
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