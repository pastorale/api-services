<?php
namespace AppBundle\Services\Core\Framework\Traits;

use Doctrine\ORM\QueryBuilder;

use FOS\RestBundle\View\View;
use Hateoas\Configuration\Route;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\Request;

trait ManipulationTrait
{
    protected $returnStatus = null;
    protected $returnRouteArray = null;
    protected $em = null;


    protected function handleSubmission(AbstractType $formType, $object, Request $request)
    {
        $form = $this->createForm($formType, $object);
        $form->handleRequest($request);
        if ($form->isValid()) {
            return $object;
        } else {
            return View::create($form, 400);
        }
    }

    protected function handleManipulation($old, $new, $fields = array(), $route = null, $routeParams = null, $autoCommit = true)
    {
        if ($old === null && $new === null) {
            throw new \Exception('both objects are null in the manipulation operation');
        }
        if ($route === null) {
            $route = 'get_' . strtolower(get_class($new));
        }
        if ($routeParams === null) {
            $routeParams = array(strtolower(get_class($new)) => $new->getId());
        }
        $this->em = $this->getDoctrine()->getManager();

        if ($old === null) {
            $this->returnStatus = 201;
            $this->returnRouteArray = array($route, $routeParams);
            return $this->handleAdd($new, $autoCommit);
        }
    }

    protected function flush($new, $msg = 'Resource updated/deleted successfully.')
    {
        if ($new === null) {
            $this->em->flush();
        } else {
            $this->em->flush($new);
        }
        if ($this->returnStatus == 201) {
            return $this->returnMessage($this->returnRouteArray, 201);
        } else {
            return $this->returnMessage('', 204);
        }
    }

    private function handleAdd($new, $autoCommit)
    {
        if ($this->container->get('security.authorization_checker')->isGranted('ADD', $new)) {
            $em = $this->em->persist($new);

            if ($autoCommit) {
                return $this->flush($new, 'New Resource added successfully');
            } else {
                return null;
            }
        } else {
            return $this->returnMessage('Unauthorised operation', 401); // if no voter, default is denied
        }
    }

    private function handleDelete($old, $autoCommit)
    {
        if ($this->container->get('security.authorization_checker')->isGranted('DELETE', $old)) {
            $this->em->remove($old);

            if ($autoCommit) {
                return $this->flush($old, 'Resource deleted successfully');
            } else {
                return null;
            }
        } else {
            return $this->returnMessage('Unauthorised operation', 401); // if no voter, default is denied
        }
    }

    private function handleEdit($new, $autoCommit)
    {
        if ($this->container->get('security.authorization_checker')->isGranted('EDIT', $new)) {
            $this->em->persist($new);

            if ($autoCommit) {
                return $this->flush($new, 'Resource edited successfully');
            } else {
                return null;
            }
        } else {
            return $this->returnMessage('Unauthorised operation', 401); // if no voter, default is denied
        }
    }

}