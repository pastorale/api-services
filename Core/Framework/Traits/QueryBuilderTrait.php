<?php
namespace AppBundle\Services\Core\Framework\Traits;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

trait QueryBuilderTrait
{

    private function processConditions(QueryBuilder $queryBuilder, $conditionArray, $alias)
    {
        if ($conditionArray !== null) {
            $autobind = true;
            if (array_key_exists('no-autobinding', $conditionArray)) {
                $autobind = false;
            }
            if (array_key_exists('where', $conditionArray)) {
                $queryBuilder->where($conditionArray['where']);
            }
            if (array_key_exists('andWheres', $conditionArray)) {
                $andWheres = $conditionArray['andWheres'];
                foreach ($andWheres as $andWhere) {
                    $queryBuilder->andWhere($andWhere);
                }
            }
            if (array_key_exists('parameters', $conditionArray)) {
                $queryBuilder->setParameters($conditionArray['parameters']);
            }
            if (array_key_exists('enabled', $conditionArray)) {
                $queryBuilder->andWhere($queryBuilder->expr()->eq($alias . '.enabled', ':trueValue'));
                if ($autobind) {
                    $queryBuilder->setParameter('trueValue', true);
                }
            }
            if (array_key_exists('not-system', $conditionArray)) {
                $queryBuilder->andWhere($queryBuilder->expr()->eq($alias . '.system', ':falseValue'));
                if ($autobind) {
                    $queryBuilder->setParameter('falseValue', false);
                }
            }
        }
        return $queryBuilder;
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
        if (is_array($alias)) {
            $selectAlias = $alias[0];
            $entityAlias = $alias[1];
        } else {
            $selectAlias = $entityAlias = $alias;
        }
        $queryBuilder
            ->select($selectAlias)
            ->from($entity, $entityAlias);
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

        $this->processConditions($queryBuilder, $conditionArray, $entityAlias);

        return $queryBuilder;
//        return $this->handlePagination($request, $queryBuilder, $routeArray[0], $routeArray[1], true);
    }


    public function prepareQBByOwningSide($owned, $ownedAlias, $owner, $ownerAlias, $ownerId, $leftJoinArray = null, $joinArray = null, $conditionArray = null, $bidirectional = true)
    {
        if ($bidirectional) {
            $joinArray[][] = $ownedAlias . '.' . $ownerAlias;
            $qB = $this->prepareCollectionQB($owned, $ownedAlias, $leftJoinArray, $joinArray, $conditionArray);
            $qB->where($qB->expr()->eq($ownerAlias . '.id', ':id'))->setParameter('id', $ownerId);
        } else { // if the owned doesn't that he/she is owned by the owner.
            if (array_key_exists('sub-query', $conditionArray)) {
                $subQueryConditionArray = $conditionArray['sub-query'];
            }

            $subQueryConditionArray['where'] = (new Expr())->eq($ownerAlias, ':ownerId');
            $ownedAlias2 = $ownedAlias . '2';
            $ownedAlias2_id = $ownedAlias2 . '.id';
            $ownedAlias_id = $ownedAlias . '.id';
            $subQB = $this->prepareCollectionQB($owner, [$ownedAlias2_id, $ownerAlias], null, [[$ownerAlias . '.' . $ownedAlias, $ownedAlias2]], $subQueryConditionArray);
//            $x = $subQB->getDQL();
//            $y = $subQB->getQuery()->getSQL();
            $qB = $this->prepareCollectionQB($owned, $ownedAlias, $leftJoinArray, $joinArray, $conditionArray);
            $qB->andWhere($qB->expr()->in($ownedAlias_id, $subQB->getDQL()));
            // parameters key does not exist it will be created automatically by PHP
            $conditionArray['parameters']['ownerId'] = $ownerId;
            // process the conditions at the top-most level
            $this->processConditions($qB, $conditionArray, $ownedAlias);

            /**
             * $qBPos = $em->createQueryBuilder();
             * $qBPos->select('tag2.id')->from('AppBundle:Organisation\Position', 'position')
             * ->join('position.employeeClasses', 'tag2')->andWhere($qBPos->expr()->eq('position', '?1'))
             * ->andWhere($qBPos->expr()->eq('tag2.enabled', '?2'))
             * ->andWhere($qBPos->expr()->eq('tag2.system', '?3'));
             *
             *
             * $queryBuilder = $em->createQueryBuilder();
             * $queryBuilder->select('tag')->from('AppBundle:Core\Classification\Tag', 'tag')
             * //            ->join('AppBundle:Organisation\Business\Business', 'business', 'WITH', '1=1')
             * //            ->join('business.tags', 'tags', 'WITH', 'tags.id = tag.id')
             * ;
             * $queryBuilder->where($queryBuilder->expr()->in('tag.id', $qBPos->getDQL()));
             * $queryBuilder->setParameters([1 => $position->getId(), 2 => true, 3 => false]);
             */
        }
        return $qB;
    }
}