<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class TaskUnitTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_task_is_correctly_identified_as_overdue(): void
    {
        $overdueTask = Task::factory()->create([
            'due_date' => now()->subDay(), // Tanggal kemarin
            'status' => 'pending',
        ]);

        $this->assertTrue($overdueTask->isOverdue());
    }

    #[Test]
    public function test_task_is_not_overdue_if_status_is_done(): void
    {
        $completedTask = Task::factory()->create([
            'due_date' => now()->subDay(), // Tanggal kemarin
            'status' => 'done',
        ]);

        $this->assertFalse($completedTask->isOverdue());
    }
}