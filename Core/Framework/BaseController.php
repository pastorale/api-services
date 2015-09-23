<?php
/**
 * Created by PhpStorm.
 * User: mayxachtayvn
 * Date: 9/23/2015
 * Time: 10:00 AM
 */

namespace AppBundle\Services\Core\Framework;


use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\FOSRestController;


use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Hateoas\Configuration\Route;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;

class BaseController extends FOSRestController
{

    protected function getContainer()
    {
        return $this->container;
    }

    protected function handleFetch($object, $msg = "Resource cannot be found")
    {
        if (isset($object)) {
            return $this->handleView($this->view($object, 200));
        } else {
            return $this->returnMessage($msg, 404);
        }
    }

    protected function returnMessage($msg, $status)
    {
        $errorMsg = ['code' => $status, 'message' => $msg];
        return $this->handleView($this->view($errorMsg, $status));
    }

    protected function filter(Request $request, QueryBuilder $queryBuilder)
    {
        $searchQuery = $request->query->get('search');
        $searches = explode(',', $searchQuery);
        foreach ($searches as $search) {
            preg_match('/(\w+?).(\w+?)(:|<|>|<=|>=)(%?\w+?%?),/', $search . ',', $matches);
            if (count($matches) == 5) {
                $objLabel = preg_replace('/[^[:alpha:]]/', '', $matches[1]);
                $valueLabel = preg_replace('/[^[:alpha:]]/', '', $matches[2]);
                $fieldLabel = $objLabel . '.' . $valueLabel;
                $paramLabel = $objLabel . '_' . $valueLabel;
                switch ($matches[3]) {
                    case ':':
                        $queryBuilder->andWhere($queryBuilder->expr()->like($fieldLabel, ':' . $paramLabel))->setParameter($paramLabel, $matches[4]);
                        break;
                    case '>':
                        $queryBuilder->andWhere($queryBuilder->expr()->gt($fieldLabel, ':' . $paramLabel))->setParameter($paramLabel, $matches[4]);
                        break;
                    case '>=':
                        $queryBuilder->andWhere($queryBuilder->expr()->gte($fieldLabel, ':' . $paramLabel))->setParameter($paramLabel, $matches[4]);
                        break;
                    case '<=':
                        $queryBuilder->andWhere($queryBuilder->expr()->lte($fieldLabel, ':' . $paramLabel))->setParameter($paramLabel, $matches[4]);
                        break;
                    case '<':
                        $queryBuilder->andWhere($queryBuilder->expr()->lt($fieldLabel, ':' . $paramLabel))->setParameter($paramLabel, $matches[4]);
                        break;
                }
            }
        }

//        $dql = $queryBuilder->getDQL();
//        $sql = $queryBuilder->getQuery()->getSQL();
        return $queryBuilder;
    }

    protected function prepare(Request $request, QueryBuilder $queryBuilder, Route $route)
    {
        $pagerfantaFactory = new PagerfantaFactory();
        // $paginatedCollection
        return $pagerfantaFactory->createRepresentation(
            $this->paginate($request, $this->filter($request, $queryBuilder)),
            $route
        );
    }

    /**
     * @param Request $request
     * @param QueryBuilder $queryBuilder
     * @return Pagerfanta
     */
    protected function paginate(Request $request, QueryBuilder $queryBuilder)
    {
        $limit = $request->query->getInt('limit');
        $limit = ($limit == 0) ? $this->container->getParameter('pagination_limit') : $limit;
        $page = ($request->query->getInt('page') == 0) ? 1 : 0;


        $sortQuery = $request->query->get('sort');
        $sorts = explode(',', $sortQuery);

        foreach ($sorts as $sort) {
            preg_match('/(\w+?).(\w+?):(asc|desc),/', $sort . ',', $matches);
            if (count($matches) == 4) {
                $queryBuilder->addOrderBy($matches[1] . '.' . $matches[2], $matches[3]);
            }
        }

        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);

        $pagerfanta->setMaxPerPage($limit);
        $pagerfanta->setCurrentPage($page);

        return $pagerfanta;
    }
}