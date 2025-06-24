<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin' || $user->role === 'manager';
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }
}