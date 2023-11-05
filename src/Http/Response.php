<?php

namespace Legend\Http;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Response extends SymfonyResponse
{

    public static function toJson(array $array)
    {
        return new static(json_encode($array));
    }
}