<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        return $user->role === 'admin' || $user->id === $task->created_by || $user->id === $task->assigned_to;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Task $task): bool
    {
        return $user->role === 'admin' || $user->id === $task->created_by;
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->role === 'admin' || $user->id === $task->created_by;
    }
}