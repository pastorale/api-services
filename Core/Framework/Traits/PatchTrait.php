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

trait PatchTrait
{

    /**
     * {"patch":[
     * {"patch 1":"hello 1"}, {"patch 2":"hello 2"}, {"patch 2":"hello 2"},
     * { "op": "add", "path": "/a/b/c", "value": [ "foo", "bar" ] }
     * ]
     * }
     *
     * {"patch":
     * [
     * { "op": "test", "path": "/a/b/c", "value": "foo" },
     * { "op": "remove", "path": "/a/b/c" },
     * { "op": "add", "path": "/a/b/c", "value": [ "foo", "bar" ] },
     * { "op": "replace", "path": "/a/b/c", "value": 42 },
     * { "op": "move", "from": "/a/b/c", "path": "/a/b/d" },
     * { "op": "copy", "from": "/a/b/d", "path": "/a/b/e" }
     * ]
     * }
     *
     */
    public function patch($object, $rootContext = '/', Request $request)
    {
        $patches = $request->request->get('patch');
        foreach ($patches as $patch) {

            if (array_key_exists('op', $patch)) {
                $op = $patch['op'];
            }
            if (array_key_exists('path', $patch)) {
                $path = explode('/', $patch['path']);
            }
            $x = $patch;
        }

        return $this->returnMessage('anh yeu em', 200);
    }

    private function add($object)
    {

    }

}