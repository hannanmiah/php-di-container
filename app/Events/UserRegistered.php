<?php

namespace App\Events;

use App\Models\User;

class UserRegistered
{
    public function __construct(User $user)
    {
    }
}