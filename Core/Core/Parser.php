<?php

namespace AppBundle\Services\Core\Core;


class Parser
{
    public static function parseClassname($name)
    {
        return array(
            'namespace' => array_slice(explode('\\', $name), 0, -1),
            'class_name' => join('', array_slice(explode('\\', $name), -1)),
        );
    }
}