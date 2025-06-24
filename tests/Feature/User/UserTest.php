<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Laravel\Sanctum\Sanctum;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->manager = User::factory()->create(['role' => 'manager']);
        $this->staff = User::factory()->create(['role' => 'staff']);
    }

    #[Test]
    public function admin_can_view_user_list(): void
    {
        Sanctum::actingAs($this->admin);
        $response = $this->getJson('/api/users');
        $response->assertStatus(200)->assertJsonCount(3);
    }

    #[Test]
    public function staff_cannot_view_user_list(): void
    {
        Sanctum::actingAs($this->staff);
        $response = $this->getJson('/api/users');
        $response->assertForbidden();
    }

    #[Test]
    public function admin_can_create_a_new_user(): void
    {
        Sanctum::actingAs($this->admin);
        $userData = [
            'name' => 'User Baru',
            'email' => 'userbaru@test.com',
            'password' => 'password123',
            'role' => 'staff',
            'status' => true,
        ];
        $response = $this->postJson('/api/users', $userData);
        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'userbaru@test.com']);
    }

    #[Test]
    public function manager_cannot_create_a_new_user(): void
    {
        Sanctum::actingAs($this->manager);
        $userData = ['name' => 'User Baru', 'email' => 'userbaru@test.com', 'password' => 'password123', 'role' => 'staff', 'status' => true];
        $response = $this->postJson('/api/users', $userData);
        $response->assertForbidden();
    }
}
