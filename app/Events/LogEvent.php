<?php

namespace App\Events;

class LogEvent
{
    public function __construct(UserLoggedIn $userLoggedIn, UserRegistered $userRegistered)
    {
    }
}