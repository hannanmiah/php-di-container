<?php

use Legend\Foundation\Application;

$app = new Application(dirname(__DIR__));

$app->singleton(
    Legend\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

return $app;