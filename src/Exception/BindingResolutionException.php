<?php

namespace Legend\Exception;

use RuntimeException;

class BindingResolutionException extends RuntimeException
{
    public function __construct(string $message = "")
    {
        parent::__construct($message);
    }
}