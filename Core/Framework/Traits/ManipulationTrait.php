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
    protected $returnedStatus = null;
    protected $returnedRouteArray = null;
    protected $em = null;


    protected function handleSubmission($formType, $object, Request $request, array $options = array())
    {
        $form = $this->createForm($formType, $object, $options);
        $form->handleRequest($request);
        if ($form->isValid()) {
            return $object;
        } else {
            return View::create($form, 400);
        }
    }

    protected function handleManipulation($old, $new, $autoCommit = true)
    {
        if ($old === null && $new === null) {
            throw new \Exception('both objects are null in the manipulation operation');
        }
        $this->em = $this->getDoctrine()->getManager();

        if ($old === null) {
            $this->returnedStatus = 201;
            return $this->handleAdd($new, $autoCommit);
        } elseif ($new === null) {
            return $this->handleDelete($old, $autoCommit);
        } else { // edit the whole object
            return $this->handleEdit($old, $new, $autoCommit);
        }
    }

    protected function flush($new, $msg = 'Resource updated/deleted successfully.')
    {
        if ($new === null) {
            $this->em->flush();
        } else {
            $this->em->flush($new);
        }
        if ($this->returnedStatus == 201) {
            $className = join('', array_slice(explode('\\', strtolower(get_class($new))), -1));
            $route = 'get_' . $className;
            $routeParams = array($className => $new->getId());

//        $this->returnedRouteArray = array($route, $routeParams);
            return $this->returnMessage(array($route, $routeParams), 201);
        } else {
            return $this->returnMessage('', 204);
        }
    }

    private
    function handleAdd($new, $autoCommit = true)
    {
        if ($isGranted = $this->container->get('security.authorization_checker')->isGranted(BaseVoter::CREATE, $new)) {
            if ($isGranted = $this->container->get('security.authorization_checker')->isGranted(BaseVoter::APPROVE, $new)) {
//                $new->setEnabled(true);
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

    private
    function handleDelete($old, $autoCommit = true)
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

    private
    function handleEdit($old, $new, $autoCommit = true)
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