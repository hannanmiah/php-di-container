<?php

namespace App\Events;

use App\Models\History;
use App\Models\User;

class UserLoggedIn
{
    public function __construct(User $user, History $history)
    {
    }
}