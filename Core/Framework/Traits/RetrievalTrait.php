<?php
namespace AppBundle\Services\Core\Framework\Traits;

use AppBundle\Model\Organisation\Handbook\Handbook;
use AppBundle\Security\Authorisation\Voter\BaseVoter;
use Doctrine\ORM\QueryBuilder;

use Hateoas\Configuration\Route;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Hateoas\Representation\PaginatedRepresentation;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Hateoas\Representation\CollectionRepresentation;

trait RetrievalTrait
{
    protected function handleFetch($object, $msg = "Resource cannot be found")
    {
        if (empty($object)) {
            return $this->returnMessage($msg, 404);
        } else {
            if ($this->container->get('security.authorization_checker')->isGranted(BaseVoter::VIEW, $object)) {
                $this->container->get('app.core.security.authority')->nullifyProperties($object);
                return $this->handleView($this->view($object, 200));
            } else {
                return $this->returnMessage('Unauthorised access', 401); // if no voter, default is denied
//                return $this->handleView($this->view($object, 200));
            }
        }
    }

    protected function handlePagination(Request $request, QueryBuilder $queryBuilder, $route, $routeParams, $fetchJoinCollection = true)
    {
        $pagerfantaFactory = new PagerfantaFactory();
        $pagerfanta = $this->paginate($request, $this->filter($request, $queryBuilder), $fetchJoinCollection);

        $currentPageResults = $pagerfanta->getCurrentPageResults();
        foreach ($currentPageResults as $object) {
            if (!$this->container->get('security.authorization_checker')->isGranted('LIST', $object)) {
                return $this->returnMessage('Unauthorised operation', 401);
            }
            break;
        }

        foreach ($currentPageResults as $object) {
            $this->container->get('app.core.security.authority')->nullifyProperties($object);
        }

        // $paginatedCollection
        return $this->handleView($this->view($pagerfantaFactory->createRepresentation(
            $pagerfanta,
            new Route($route, $routeParams)
        )));
    }

    protected function handlePaginationWithModelHandbook(Request $request, QueryBuilder $queryBuilder, $route, $routeParams, $fetchJoinCollection = true)
    {
        $pagerfantaFactory = new PagerfantaFactory();
        $pagerfanta = $this->paginate($request, $this->filter($request, $queryBuilder), $fetchJoinCollection);

        $currentPageResults = $pagerfanta->getCurrentPageResults();
        foreach ($currentPageResults as $object) {
            if (!$this->container->get('security.authorization_checker')->isGranted('LIST', $object)) {
                return $this->returnMessage('Unauthorised operation', 401);
            }
            break;
        }

        foreach ($currentPageResults as $object) {
            $this->container->get('app.core.security.authority')->nullifyProperties($object);
        }


        $pager = $pagerfanta;
        $route = new Route($route, $routeParams);

        $user = $request->get('user');
        $modelHandbooks = new \ArrayIterator();
        foreach ($currentPageResults as $object) {
            $modelHandbook = new Handbook();
            $modelHandbook->setId($object->getId());
            $modelHandbook->setTitle($object->getTitle());
            $modelHandbook->setOrganisation($object->getOrganisation()->getId());
            $modelHandbook->setBlocked($this->container->get('app.core.security.acl')->isBlocked(null, $object,$user));

            //add more field to model handbook from handbook entity here

            //

            $modelHandbooks->append($modelHandbook);

        }

        $inline = new CollectionRepresentation($modelHandbooks);
        $view = new PaginatedRepresentation(
            $inline,
            $route->getName(),
            $route->getParameters(),
            $pager->getCurrentPage(),
            $pager->getMaxPerPage(),
            $pager->getNbPages(),
            null,
            null,
            $route->isAbsolute(),
            $pager->getNbResults()
        );

        // $paginatedCollection
        return $this->handleView($this->view($view));
    }

    /**
     * @return null|array
     */
    protected function getChildClasses()
    {
        return null;
    }

    protected
    function filter(Request $request, QueryBuilder $queryBuilder)
    {
        $searchQuery = $request->query->get('search');
        $searches = explode(',', $searchQuery);
        foreach ($searches as $search) {
            // test string: user.email:dinis.bean-123df@dfecio.yahoo.com,   https://regex101.com/#pcre
            preg_match('/(\w+?.\w+?)(:|!:|<|>|<=|>=|==|!=|{null}|{instance-of})(%?[a-zA-Z0-9_.@-]+?%?),/', $search . ',', $matches);

            //            preg_match('/(\w+?).(\w+?)(:|!:|<|>|<=|>=|==|!=|{null})(%?\w+?%?),/', $search . ',', $matches);
//            preg_match('/(\w+?).(\w+?)(:|!:|<|>|<=|>=|==|!=|{null})(%?\w+( +\w+)+%?),/', $search . ',', $matches); // to match a phrase
            if (count($matches) == 4) {
                $objLabel = preg_replace('/[^[:alpha:]]/', '', $matches[1]);
//                $valueLabel = preg_replace('/[^[:alpha:]]/', '', $matches[2]);
//                $fieldLabel = $objLabel . '.' . $valueLabel;
//                $paramLabel = $objLabel . '_' . $valueLabel;
                $fieldLabel = $paramLabel = preg_replace('/[^[a-zA-Z0-9_.@-]]/', '', $matches[1]);
                $paramLabel = preg_replace('/[.]/', '_', $paramLabel);
                $searchValue = $matches[3];
                $comparison = $matches[2];
                switch ($comparison) {
                    case '{instance-of}':
                        $childClasses = $this->getChildClasses();
                        if ($childClasses === null) {
                            break;
                        }
                        $children = explode(',', $searchValue);
                        foreach ($children as $child) {
                            $queryBuilder->andWhere($objLabel . ' INSTANCE OF  :childClass' . ucfirst($child))->setParameter('childClass' . ucfirst($child), $childClasses[$child]);
                        }
                        break;
                    case '{null}':
                        if ($searchValue) {
                            $queryBuilder->andWhere($queryBuilder->expr()->isNull($fieldLabel));
                        } else {
                            $queryBuilder->andWhere($queryBuilder->expr()->isNotNull($fieldLabel));
                        }
                        break;
                    case '!=':
                        $queryBuilder->andWhere($queryBuilder->expr()->neq($fieldLabel, ':' . $paramLabel))->setParameter($paramLabel, $searchValue);
                        break;
                    case '==':
                        $queryBuilder->andWhere($queryBuilder->expr()->eq($fieldLabel, ':' . $paramLabel))->setParameter($paramLabel, $searchValue);
                        break;
                    case '!:':
                        $queryBuilder->andWhere($queryBuilder->expr()->notLike($fieldLabel, ':' . $paramLabel))->setParameter($paramLabel, $searchValue);
                        break;
                    case ':':
                        $queryBuilder->andWhere($queryBuilder->expr()->like($fieldLabel, ':' . $paramLabel))->setParameter($paramLabel, $searchValue);
                        break;
                    case '>':
                        $queryBuilder->andWhere($queryBuilder->expr()->gt($fieldLabel, ':' . $paramLabel))->setParameter($paramLabel, $searchValue);
                        break;
                    case '>=':
                        $queryBuilder->andWhere($queryBuilder->expr()->gte($fieldLabel, ':' . $paramLabel))->setParameter($paramLabel, $searchValue);
                        break;
                    case '<=':
                        $queryBuilder->andWhere($queryBuilder->expr()->lte($fieldLabel, ':' . $paramLabel))->setParameter($paramLabel, $searchValue);
                        break;
                    case '<':
                        $queryBuilder->andWhere($queryBuilder->expr()->lt($fieldLabel, ':' . $paramLabel))->setParameter($paramLabel, $searchValue);
                        break;
                }
            }
        }


//        $dql = $queryBuilder->getDQL();
//        $sql = $queryBuilder->getQuery()->getSQL();


        return $queryBuilder;
    }

    /**
     * @deprecated
     * @param Request $request
     * @param QueryBuilder $queryBuilder
     * @param $route
     * @param $routeParams
     * @param bool|true $fetchJoinCollection
     * @return PaginatedRepresentation
     */
    protected
    function prepare(Request $request, QueryBuilder $queryBuilder, $route, $routeParams, $fetchJoinCollection = true)
    {
        $pagerfantaFactory = new PagerfantaFactory();
        // $paginatedCollection
        return $pagerfantaFactory->createRepresentation(
            $this->paginate($request, $this->filter($request, $queryBuilder), $fetchJoinCollection),
            new Route($route, $routeParams)
        );
    }

    /**
     * @param Request $request
     * @param QueryBuilder $queryBuilder
     * @param bool $fetchJoinCollection
     * @return Pagerfanta
     */
    protected
    function paginate(Request $request, QueryBuilder $queryBuilder, $fetchJoinCollection = true, $useOutputWalkers = false)
    {
        $limit = $request->query->getInt('limit');
        $page = $request->query->getInt('page');
        $limit = ($limit == 0) ? $this->container->getParameter('pagination_limit') : $limit;
        $page = ($page == 0) ? 1 : $page;

        $sortQuery = $request->query->get('sort');
        $sorts = explode(',', $sortQuery);

        foreach ($sorts as $sort) {
            preg_match('/(\w+?).(\w+?):(asc|desc),/', $sort . ',', $matches);
            if (count($matches) == 4) {
                $queryBuilder->addOrderBy($matches[1] . '.' . $matches[2], $matches[3]);
            }
        }

        $adapter = new DoctrineORMAdapter($queryBuilder, $fetchJoinCollection, $useOutputWalkers);
        $pagerfanta = new Pagerfanta($adapter);

        $pagerfanta->setMaxPerPage($limit);
        $pagerfanta->setCurrentPage($page);

        return $pagerfanta;
    }
}