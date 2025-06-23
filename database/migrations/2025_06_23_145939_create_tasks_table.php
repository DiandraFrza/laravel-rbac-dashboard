<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary(); // 
            $table->string('title'); // 
            $table->text('description')->nullable(); // 
            $table->foreignUuid('assigned_to')->constrained('users'); // 
            $table->enum('status', ['pending', 'in_progress', 'done'])->default('pending'); // 
            $table->date('due_date'); // 
            $table->foreignUuid('created_by')->constrained('users'); // 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
