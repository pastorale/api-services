<?php
namespace AppBundle\Services\Core\Framework;

use Doctrine\ORM\QueryBuilder;
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

    public function fetchCollection($entity, $alias, $routeArray, $leftJoinArray = null, $joinArray = null)
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        $queryBuilder = $em->createQueryBuilder();

//        $queryBuilder = new QueryBuilder();

        $queryBuilder
            ->select($alias)
            ->from($entity, $alias);
        if ($leftJoinArray !== null) {
            foreach ($leftJoinArray as $leftJoin) {
                $field = $leftJoin[0];
                if (array_key_exists(1, $leftJoin)) {
                    $fieldAlias = $leftJoin[1];
                } else {
                    $fieldAlias = $field;
                }
                $queryBuilder->leftJoin($alias . '.' . $field, $fieldAlias);
            }
        }

// todo join array here

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
    public function fetchByOwningSide($owned, $alias, $owner, $id, $routeArray, $leftJoinArray = null, $joinArray = null)
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->createQueryBuilder()
            ->select($alias)
            ->from($owned, $alias);

//        $queryBuilder = new QueryBuilder();

        if ($leftJoinArray !== null) {
            foreach ($leftJoinArray as $leftJoin) {
                $field = $leftJoin[0];
                if (array_key_exists(1, $leftJoin)) {
                    $fieldAlias = $leftJoin[1];
                } else {
                    $fieldAlias = $field;
                }
                $queryBuilder->leftJoin($alias . '.' . $field, $fieldAlias);
            }
        }

        if ($joinArray !== null) {
            $field = null;
            foreach ($joinArray as $join) {
                $field = $join[0];
                if (array_key_exists(1, $join)) {
                    $fieldAlias = $join[1];
                } else {
                    $fieldAlias = $field;
                }
                if (array_key_exists(2, $join)) {
                    $queryBuilder->join($field, $fieldAlias, 'WITH', $join[2]);
                } else {
                    $queryBuilder->join($field, $fieldAlias);
                }
            }
            $queryBuilder->where($owner . '.id' . '= ?1');
        } else {
            $queryBuilder->where($alias . '.' . $owner . '= ?1');
        }


        $queryBuilder
            ->setParameter(1, $id);
        return ($this->handlePagination($request, $queryBuilder, $routeArray[0], $routeArray[1], false));
    }

}