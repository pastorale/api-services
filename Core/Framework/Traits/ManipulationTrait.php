<?php
namespace AppBundle\Services\Core\Framework\Traits;

use AppBundle\Security\Authorisation\Voter\BaseVoter;
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


    protected function handleSubmission(AbstractType $formType, $object, Request $request, array $options = array())
    {
        $form = $this->createForm($formType, $object, $options);
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
        } elseif ($new === null) {
            return $this->handleDelete($old, $autoCommit);
        } elseif (count($fields) === 0) { // edit the whole object
            return $this->handleEdit($old, $new, $autoCommit);
        } else {

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
        if ($this->container->get('security.authorization_checker')->isGranted(BaseVoter::CREATE, $new)) {
            if ($this->container->get('security.authorization_checker')->isGranted(BaseVoter::APPROVE, $new)) {
                $new->setEnabled(true);
            } else {
                $new->setEnabled(false);
            }
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
        if ($this->container->get('security.authorization_checker')->isGranted(BaseVoter::DELETE, $old)) {
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

    private function handleEdit($old, $new, $autoCommit)
    {
        if ($this->container->get('security.authorization_checker')->isGranted(BaseVoter::EDIT, $new)) {
            if ($this->container->get('security.authorization_checker')->isGranted(BaseVoter::APPROVE, $new)) {

            } else {
                $new->setEnabled($old->isEnabled());
            }
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