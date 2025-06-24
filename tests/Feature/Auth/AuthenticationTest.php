<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;
    #[Test] 
    public function test_user_can_login_and_get_token(): void
    {
        $this->seed();

        $response = $this->postJson('/api/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'message',
            'access_token',
            'token_type',
            'user' => [
                'id',
                'name',
                'email',
                'role',
                'status'
            ]
        ]);
    }
    #[Test]
    public function test_user_cannot_access_protected_route_without_token(): void
    {
        // Coba akses /api/user tanpa login/token
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }
    #[Test]
    public function test_user_can_access_protected_route_with_token(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/user');

        $response->assertStatus(200);
    }
}