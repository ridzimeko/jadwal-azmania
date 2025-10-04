<?php

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
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
        Schema::create('jadwal_pelajarans', function (Blueprint $table) {
            $table->id();
            $table->time('hari');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->foreignIdFor(Kelas::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Guru::class)->nullable()->constrained()->onDelete('cascade');
            $table->foreignIdFor(MataPelajaran::class)->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_pelajarans');
    }
};
