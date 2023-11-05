<?php

use App\Events\LogEvent;
use App\Events\UserLoggedIn;
use App\Events\UserRegistered;

require __DIR__ . '/../vendor/autoload.php';


$app = require __DIR__ . '/../bootstrap/app.php';

$app->singleton('log', function ($container) {
    return new LogEvent($container->make(UserLoggedIn::class), $container->make(UserRegistered::class));
});

dd($app);