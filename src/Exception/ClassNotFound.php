<?php

namespace Legend\Exception;

use Psr\Container\NotFoundExceptionInterface;

class ClassNotFound extends \RuntimeException implements NotFoundExceptionInterface
{

    public function __construct (string $class)
    {
        parent::__construct("Class {$class} not found.");
    }
}