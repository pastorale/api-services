<?php
namespace AppBundle\Services\Core\Framework;

use FOS\RestBundle\Routing\ClassResourceInterface;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Response;

class ControllerService extends BaseController implements ClassResourceInterface
{
//    use ContainerConstructorTrait;

    public function optionsAction()
    {
        $response = new Response();
        $response->headers->set('Allow', 'OPTIONS, GET, PATCH, POST, PUT, DELETE');
        return $response;
    }


}