<?php

namespace AppBundle\Services\Core\Framework;

use AppBundle\Services\Core\Core\Parser;
use AppBundle\Services\Core\Framework\Traits\ManipulationTrait;
use AppBundle\Services\Core\Framework\Traits\PatchTrait;
use AppBundle\Services\Core\Framework\Traits\QBSecurityTrait;
use AppBundle\Services\Core\Framework\Traits\QueryBuilderTrait;
use AppBundle\Services\Core\Framework\Traits\RetrievalTrait;
use FOS\RestBundle\Controller\FOSRestController;

use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

class BaseController extends FOSRestController
{

    use RetrievalTrait;
    use QueryBuilderTrait;
    use QBSecurityTrait;
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

    protected function commitPostPut($formType, $entityInstance, $post = true, $routeArray = null, Request $request, $parent = array())
    {
        $parentInstance = array_key_exists('instance', $parent) ? $parent['instance'] : null;

        $entityClassName = get_class($entityInstance);

        if ($formType !== null) {
            $object = $this->handleSubmission($formType, $entityInstance, $request, $post ? array() : array('method' => 'PUT'));
        } else {
            $object = $entityInstance;
        }
//        if ($object instanceof $entityClassName) {}
        if ($object instanceof View) {
            return $object;
        }
        if ($parentInstance !== null) {
            $parentClassNameArray = Parser::parseClassname(get_class($parentInstance));
            $parentPropName = array_key_exists('property_name', $parent) ? $parent['property_name'] : strtolower($parentClassNameArray['class_name']);

//                call_user_func([$entityClassName, 'set' . ucfirst($parentPropName)], $entityInstance);
            $getParentMethod = 'get' . ucfirst($parentPropName);
            $parentInstanceFromEntity = $entityInstance->$getParentMethod();
            $entityShortClassName = Parser::parseClassname($entityClassName)['class_name'];
            if ($parentInstanceFromEntity !== null) {
                if ($parentInstanceFromEntity->getId() !== $parentInstance->getId()) {
                    $removeChildMethod = 'remove' . $entityShortClassName;
                    $parentInstanceFromEntity->$removeChildMethod($entityInstance);
                }
            }
            $addChildMethod = 'add' . $entityShortClassName;
            $parentInstance->$addChildMethod($entityInstance);
        }
        if ($post) {
            return $this->handleManipulation(null, $object, $routeArray);
        } else {
            return $this->handleManipulation($entityInstance, $object, $routeArray);
        }
    }
}

}