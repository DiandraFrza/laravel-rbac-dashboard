<?php

namespace Tests\Feature\Log;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\ActivityLog;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_view_activity_logs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        ActivityLog::factory()->create(['user_id' => $admin->id]);

        Sanctum::actingAs($admin);
        $response = $this->getJson('/api/logs');
        $response->assertStatus(200)->assertJsonCount(1);
    }

    #[Test]
    public function manager_cannot_view_activity_logs(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        
        Sanctum::actingAs($manager);
        $response = $this->getJson('/api/logs');
        $response->assertForbidden();
    }
}
