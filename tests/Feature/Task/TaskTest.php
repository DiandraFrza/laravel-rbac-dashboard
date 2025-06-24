<?php

namespace Tests\Feature\Task;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use Laravel\Sanctum\Sanctum;

class TaskTest extends TestCase
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

    public function test_manager_can_create_task_for_staff(): void
    {
        Sanctum::actingAs($this->manager);

        $taskData = [
            'title' => 'Siapkan Laporan Bulanan',
            'description' => 'Tolong siapkan laporan penjualan untuk bulan Juni.',
            'assigned_to' => $this->staff->id, // Tugaskan ke staff
            'due_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => 'pending',
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Siapkan Laporan Bulanan',
            'assigned_to' => $this->staff->id,
        ]);
    }

    public function test_staff_cannot_delete_task_they_did_not_create(): void
    {
        $task = Task::factory()->create([
            'created_by' => $this->manager->id,
            'assigned_to' => $this->staff->id,
        ]);

        Sanctum::actingAs($this->staff);

        $response = $this->deleteJson('/api/tasks/' . $task->id);

        $response->assertForbidden();
    }

    public function test_admin_can_delete_any_task(): void
    {
        $task = Task::factory()->create([
            'created_by' => $this->manager->id,
            'assigned_to' => $this->staff->id,
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson('/api/tasks/' . $task->id);
        
        $response->assertStatus(204);

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }
}