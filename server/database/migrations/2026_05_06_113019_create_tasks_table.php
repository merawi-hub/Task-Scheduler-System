<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('scheduler_jobs')->onDelete('cascade');
            $table->unsignedInteger('task_index');
            $table->json('payload');
            $table->enum('status', ['pending', 'assigned', 'running', 'done', 'failed', 'cancelled'])->default('pending');
            $table->foreignId('worker_id')->nullable()->constrained('workers')->onDelete('set null');
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->unsignedTinyInteger('max_retries')->default(3);
            $table->text('failure_reason')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('available_after')->nullable();
            $table->unsignedInteger('timeout_seconds')->default(300);
            $table->timestamps();
            
            $table->index(['status', 'available_after']);
            $table->index('job_id');
            $table->index('worker_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
