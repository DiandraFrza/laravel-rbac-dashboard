<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon; // Tanggal

class CheckOverdueTasks extends Command
{
    protected $signature = 'tasks:check-overdue';

    protected $description = 'Check for overdue tasks and log them into activity_logs';

    public function handle()
    {
        $this->info('Mulai pengecekan task yang telat...');

        $overdueTasks = Task::where('due_date', '<', Carbon::today())
                            ->where('status', '!=', 'done')
                            ->get();

        if ($overdueTasks->isEmpty()) {
            $this->info('Tidak ada task yang telat ditemukan. Kerja bagus!');
            return 0; // Succes
        }

        $this->info("Ditemukan {$overdueTasks->count()} task yang telat. Mencatat ke log...");

        $systemUser = User::where('role', 'admin')->first();
        if (!$systemUser) {
            $this->error('Tidak ada user admin untuk mencatat log. Aksi dibatalkan.');
            return 1;
        }

        foreach ($overdueTasks as $task) {
            ActivityLog::create([
                'user_id' => $systemUser->id,
                'action' => 'TASK_OVERDUE',
                'description' => "Task overdue: " . $task->id,
            ]);
            $this->line("- Task '{$task->title}' (ID: {$task->id}) dicatat sebagai overdue.");
        }

        $this->info('Selesai mencatat semua task yang telat.');
        return 0;
    }
}