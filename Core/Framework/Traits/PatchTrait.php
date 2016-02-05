<?php
namespace AppBundle\Services\Core\Framework\Traits;

use AppBundle\Security\Authorisation\Authority;
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
     * {"patch":[
     * { "op": "add", "path": "", "value": [] },
     * => path = [0=>""], value = []
     * { "op": "add", "path": "/", "value": "" },
     * => path = [0=>"",1=>""], value = ""
     * { "op": "add", "path": "/a/b/c", "value": [ "foo", "bar" ] }
     * ]
     * }
     */

    protected function isGranted($attribute, $property = NULL)
    {
        return true;
    }

    /**
     * this assume that the path is the same as the entity's attribute.
     * @param $object
     * @param string $rootContext
     * @param Request $request
     * @return mixed
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

            $reader = $this->container->get('annotation_reader');
            $authChecker = $this->container->get('security.authorization_checker');

            $class = get_class($object);
            $proxyStr = Authority::PROXY_PREFIX;
            if (($pos = strpos($class, $proxyStr)) === FALSE) {
                $reflectionObject = new \ReflectionObject($object);
            } else {
                $class = substr($class, strlen($proxyStr));
                $reflectionObject = new \ReflectionObject(new $class);
            }

            $reflectionProperties = $reflectionObject->getProperties();// get props
//Start of annotations reading
            foreach ($reflectionProperties as $property) {
                if (!$authChecker->isGranted('EDIT', $object)) {
                    if ($this->isGranted('EDIT', $property)) { // todo really
                        if (preg_match('/@var\s+([^\s]+)/', $property->getDocComment(), $matches)) {
                            list(, $type) = $matches;
                            if (in_array($type, self::NON_ENTITY_TYPES)) {
                                if ($property->getName() == 'id') {
                                    call_user_func_array(array($object, 'set' . ucfirst($property->getName())), array(0));
                                } else {
                                    call_user_func_array(array($object, 'set' . ucfirst($property->getName())), array(null));
                                }
                                continue;
                            }
                        }
                    }
                }
            }

        }

        return $this->returnMessage('anh yeu em', 200);
    }

    private function add($object,$property)
    {

    }

}