<?php
namespace AppBundle\Services\Core\Framework;

use FOS\RestBundle\Routing\ClassResourceInterface;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
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

    public function fetchCollection($entity, $alias, $leftJoinArray, $routeArray)
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->createQueryBuilder()
            ->select($alias)
            ->from($entity, $alias);
        foreach ($leftJoinArray as $leftJoin) {
            $field = $leftJoin[0];
            if (array_key_exists(1, $leftJoin)) {
                $fieldAlias = $leftJoin[1];
            }else{
                $fieldAlias = $field;
            }
            $queryBuilder->leftJoin($alias . '.' . $field, $fieldAlias);
        }

        return $this->handlePagination($request, $queryBuilder, $routeArray[0], $routeArray[1], true);
    }

    /**
     * @param string $owned
     * @param string $alias
     * @param string $owner
     * @param int $id
     * @param array $routeArray
     * @return mixed
     */
    public function fetchByOwningSide($owned, $alias, $owner, $id, $routeArray)
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->createQueryBuilder()
            ->select($alias)
            ->from($owned, $alias)
            ->where($alias . '.' . $owner . '= ?1')
            ->setParameter(1, $id);
        return ($this->handlePagination($request, $queryBuilder, $routeArray[0], $routeArray[1], false));
    }

}