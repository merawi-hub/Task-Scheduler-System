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
        // Add image processing fields to scheduler_jobs table
        Schema::table('scheduler_jobs', function (Blueprint $table) {
            $table->json('input_files')->nullable()->after('type');
            $table->json('output_files')->nullable()->after('input_files');
            $table->json('operations')->nullable()->after('output_files');
            $table->string('storage_path')->nullable()->after('operations');
        });

        // Add image processing fields to tasks table
        Schema::table('tasks', function (Blueprint $table) {
            $table->json('input_images')->nullable()->after('payload');
            $table->json('output_images')->nullable()->after('input_images');
            $table->integer('images_processed')->default(0)->after('output_images');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scheduler_jobs', function (Blueprint $table) {
            $table->dropColumn(['input_files', 'output_files', 'operations', 'storage_path']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['input_images', 'output_images', 'images_processed']);
        });
    }
};
