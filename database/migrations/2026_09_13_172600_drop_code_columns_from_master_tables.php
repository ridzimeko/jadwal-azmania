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
            $table->dropUnique(['kode_mapel']);
            $table->dropColumn('kode_mapel');
        });

        Schema::table('guru', function (Blueprint $table) {
            $table->dropUnique(['kode_guru']);
            $table->dropColumn('kode_guru');
        });

        Schema::table('kelas', function (Blueprint $table) {
            $table->dropUnique(['kode_kelas']);
            $table->dropColumn('kode_kelas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            $table->string('kode_mapel', 12)->nullable()->unique();
        });

        Schema::table('guru', function (Blueprint $table) {
            $table->string('kode_guru', 12)->nullable()->unique();
        });

        Schema::table('kelas', function (Blueprint $table) {
            $table->string('kode_kelas', 12)->nullable()->unique();
        });
    }
};
