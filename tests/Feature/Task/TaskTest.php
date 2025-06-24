<?php

namespace Tests\Feature\Task;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\User;
use App\Models\Task;
use Laravel\Sanctum\Sanctum;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $staff;
    private User $anotherStaff;

    #[Test]
    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->manager = User::factory()->create(['role' => 'manager']);
        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->anotherStaff = User::factory()->create(['role' => 'staff']);
    }

    #[Test]
    public function staff_can_only_view_their_assigned_or_created_tasks(): void
    {
        Task::factory()->create([
            'created_by' => $this->manager->id,
            'assigned_to' => $this->staff->id,
        ]);

        Task::factory()->create([
            'created_by' => $this->manager->id,
            'assigned_to' => $this->anotherStaff->id,
        ]);

        Sanctum::actingAs($this->staff);

        $response = $this->getJson('/api/tasks');

        $response->assertStatus(200)
                 ->assertJsonCount(1);
    }

    #[Test]
    public function user_can_view_a_specific_task_with_permission(): void
    {
        $task = Task::factory()->create([
            'created_by' => $this->manager->id,
            'assigned_to' => $this->staff->id,
        ]);

        Sanctum::actingAs($this->staff);

        $response = $this->getJson('/api/tasks/' . $task->id);

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => $task->title]);
    }

    #[Test]
    public function task_creator_can_update_the_task(): void
    {
        $task = Task::factory()->create([
            'created_by' => $this->manager->id,
            'assigned_to' => $this->staff->id,
            'status' => 'pending'
        ]);

        Sanctum::actingAs($this->manager);

        $response = $this->putJson('/api/tasks/' . $task->id, [
            'status' => 'in_progress'
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['status' => 'in_progress']);
        
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'in_progress'
        ]);
    }
}
