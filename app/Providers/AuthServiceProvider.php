<?php

namespace App\Providers;

use App\Models\ActivityLog; 
use App\Models\Task;
use App\Models\User;
use App\Policies\ActivityLogPolicy; 
use App\Policies\TaskPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Task::class => TaskPolicy::class,
        User::class => UserPolicy::class,
        ActivityLog::class => ActivityLogPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}