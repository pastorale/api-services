<?php
// src/Solutions/AppBundle/Services/ContainerConstructorTrait.php

namespace AppBundle\Services\Traits;

use Symfony\Component\DependencyInjection\ContainerInterface;

trait ContainerConstructorTrait
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}