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
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            if (Schema::hasColumn('mata_pelajaran', 'jp_per_pekan')) {
                $table->dropColumn('jp_per_pekan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            $table->integer('jp_per_pekan')->default(0);
        });
    }
};
