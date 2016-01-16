<?php

namespace AppBundle\Services\Core\Framework;

use AppBundle\Services\Core\Framework\Traits\ManipulationTrait;
use AppBundle\Services\Core\Framework\Traits\RetrievalTrait;
use FOS\RestBundle\Controller\FOSRestController;

use Symfony\Component\HttpFoundation\Request;

class BaseController extends FOSRestController
{
    use RetrievalTrait;
    use ManipulationTrait;

    /**
     * {"patch":[
     * {"patch 1":"hello 1"}, {"patch 2":"hello 2"}, {"patch 2":"hello 2"},
     * { "op": "add", "path": "/a/b/c", "value": [ "foo", "bar" ] }
     * ]
     * }
     *
     */
    public function patch($rootContext = '/', Request $request)
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

    protected function getContainer()
    {
        return $this->container;
    }


    protected function returnMessage($msg, $status)
    {
        if ($status == 201) {
            $response = $this->handleView($this->view(['code' => $status, 'message' => ''], $status));
            $response->headers->set('Location', $this->get('router')->generate($msg[0], $msg[1]));
        } else {
            $response = $this->handleView($this->view(['code' => $status, 'message' => $msg], $status));
        }

        return $response;
    }

}