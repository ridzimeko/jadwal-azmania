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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('action'); // 'create', 'update', 'delete'
            $table->string('module')->nullable(); // Friendly name e.g. 'Mata Pelajaran', 'Jadwal Pelajaran', 'Admin'
            $table->string('subject_type')->nullable(); // Model class name
            $table->string('subject_id')->nullable();
            $table->string('description');
            $table->json('properties')->nullable(); // { "old": {...}, "new": {...} }
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
