<?php

namespace App\Policies;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ActivityLogPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }
}