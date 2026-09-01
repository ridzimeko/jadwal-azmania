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
        try {
            Schema::table('periode', function (Blueprint $table) {
                $table->unique(['tahun_ajaran', 'semester']);
            });
        } catch (\Throwable $e) {
            // Index may already exist from table creation
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('periode', function (Blueprint $table) {
            $table->dropUnique(['tahun_ajaran', 'semester']);
        });
    }
};
