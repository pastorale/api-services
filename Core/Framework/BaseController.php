<?php

namespace AppBundle\Services\Core\Framework;

use AppBundle\Services\Core\Framework\Traits\ManipulationTrait;
use AppBundle\Services\Core\Framework\Traits\PatchTrait;
use AppBundle\Services\Core\Framework\Traits\QueryBuilderTrait;
use AppBundle\Services\Core\Framework\Traits\RetrievalTrait;
use FOS\RestBundle\Controller\FOSRestController;

use Symfony\Component\HttpFoundation\Request;

class BaseController extends FOSRestController
{

    use RetrievalTrait;
    use QueryBuilderTrait;
    use ManipulationTrait;
    use PatchTrait;

    protected function getContainer()
    {
        return $this->container;
    }

    public function getRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }

    protected function returnMessage($msg, $status = 200)
    {
        if ($status == 201) {
            $response = $this->handleView($this->view(['code' => $status, 'message' => ''], $status));
            $response->headers->set('Location', $this->get('router')->generate($msg[0], $msg[1]));
        } else {
            $response = $this->handleView($this->view(['code' => $status, 'message' => $msg], $status));
        }

        return $response;
    }

    protected function commitPostPut($formType, $entityInstance, $post = true, Request $request)
    {
        $entityClassName = get_class($entityInstance);
        $object = $this->handleSubmission($formType, $entityInstance, $request, $post ? array() : array('method' => 'PUT'));
        if ($object instanceof $entityClassName) {
            return $this->handleManipulation(null, $object);
        } else {
            return $object;
        }
    }

}