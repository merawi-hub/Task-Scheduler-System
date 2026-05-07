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
        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            $table->string('worker_key', 64)->unique();
            $table->string('hostname', 255);
            $table->string('ip_address', 45)->nullable();
            $table->enum('status', ['idle', 'busy', 'dead'])->default('idle');
            $table->unsignedBigInteger('current_task_id')->nullable();
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->unsignedInteger('tasks_completed')->default(0);
            $table->unsignedInteger('tasks_failed')->default(0);
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamps();
            
            $table->index('worker_key');
            $table->index('status');
            $table->index('last_heartbeat_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workers');
    }
};
