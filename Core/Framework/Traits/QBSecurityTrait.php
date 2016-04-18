<?php
namespace AppBundle\Services\Core\Framework\Traits;

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

trait QBSecurityTrait
{
    public function secureQB(Request $request, QueryBuilder $qb, $entityAlias = null, $conditions = null)
    {
        if (is_array($conditions)) {
            if (array_key_exists('admin', $conditions)) {
                $admin = $conditions['admin'];
            } elseif (array_key_exists('owner', $conditions)) {
                $owner = $conditions['owner'];
            } elseif (array_key_exists('enabled', $conditions)) {
                $enabled = $conditions['enabled'];
            }

        }
        return $qb;
    }
}