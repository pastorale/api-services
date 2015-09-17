<?php
namespace AppBundle\Services\Core\Framework;

use AppBundle\Traits\ContainerConstructorTrait;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ControllerService extends Controller
{
    use ContainerConstructorTrait;

    public function getContainer()
    {
        return $this->container;
    }
}