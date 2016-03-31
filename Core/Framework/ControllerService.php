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

    /**
     * @param $entity
     * @param $alias
     * @param null $leftJoinArray
     * @param null $joinArray
     * @param null $conditionArray
     * @return QueryBuilder
     */

    public function prepareCollectionQB($entity, $alias, $leftJoinArray = null, $joinArray = null, $conditionArray = null)
    {
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
                    $fieldAlias = explode('.', $field)[1];
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
                    $fieldAlias = explode('.', $field)[1];
                }
                if (array_key_exists(2, $join)) {
                    $queryBuilder->join($field, $fieldAlias, 'WITH', $join[2]);
                } else {
                    $queryBuilder->join($field, $fieldAlias);
                }
            }
        }

        if ($conditionArray !== null) {
            if (array_key_exists('where', $conditionArray)) {
                $queryBuilder->where($conditionArray['where']);
            }
            if (array_key_exists('parameters', $conditionArray)) {
                $queryBuilder->setParameters($conditionArray['parameters']);
            }
            if (array_key_exists('enabled', $conditionArray)) {
                $queryBuilder->andWhere($queryBuilder->expr()->eq($alias . '.enabled', ':trueValue'))->setParameter('trueValue', true);
            }
            if (array_key_exists('not-system', $conditionArray)) {
                $queryBuilder->andWhere($queryBuilder->expr()->eq($alias . '.system', ':falseValue'))->setParameter('falseValue', false);
            }
        }

        return $queryBuilder;
//        return $this->handlePagination($request, $queryBuilder, $routeArray[0], $routeArray[1], true);
    }


    public function prepareQBByOwningSide($owned, $ownedAlias, $owner, $ownerAlias, $ownerId, $leftJoinArray = null, $joinArray = null, $conditionArray = null, $bidirectional = true)
    {
        if ($bidirectional) {
            $joinArray[][] = $ownedAlias . '.' . $ownerAlias;
            $qB = $this->prepareCollectionQB($owned, $ownedAlias, $leftJoinArray, $joinArray, $conditionArray);
            $qB->where($qB->expr()->eq($ownerAlias . '.id', ':id'))->setParameter('id', $ownerId);
            return $qB;
        } else {

        }
//        $request = $this->getRequest();
//        $em = $this->getDoctrine()->getManager();
//        $queryBuilder = $em->createQueryBuilder()
//            ->select($ownedAlias)
//            ->from($owned, $ownedAlias);

//        $queryBuilder = new QueryBuilder();
//
//        if ($leftJoinArray !== null) {
//            foreach ($leftJoinArray as $leftJoin) {
//                $field = $leftJoin[0];
//                if (array_key_exists(1, $leftJoin)) {
//                    $fieldAlias = $leftJoin[1];
//                } else {
//                    $fieldAlias = $field;
//                }
//                $queryBuilder->leftJoin($ownedAlias . '.' . $field, $fieldAlias);
//            }
//        }
//
//        if ($joinArray !== null) {
//            $field = null;
//            foreach ($joinArray as $join) {
//                $field = $join[0];
//                if (array_key_exists(1, $join)) {
//                    $fieldAlias = $join[1];
//                } else {
//                    $fieldAlias = $field;
//                }
//                if (array_key_exists(2, $join)) {
//                    $queryBuilder->join($field, $fieldAlias, 'WITH', $join[2]);
//                } else {
//                    $queryBuilder->join($field, $fieldAlias);
//                }
//            }
//            $queryBuilder->where($ownerAlias . '.id' . '= ?1');
//        } else {
//            $queryBuilder->where($ownedAlias . '.' . $ownerAlias . '= ?1');
//        }


//        $queryBuilder
//            ->setParameter(1, $id);
//        return ($this->handlePagination($request, $queryBuilder, $routeArray[0], $routeArray[1], false));
    }

}