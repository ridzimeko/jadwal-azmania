<?php

use App\Models\Guru;
use App\Models\Kegiatan;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Periode;
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
        Schema::create('jadwal_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->enum('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->enum('kategori', ['pelajaran', 'kegiatan']);
            $table->foreignIdFor(Kegiatan::class)->nullable()->constrained()->onDelete('set null');
            $table->foreignIdFor(MataPelajaran::class)->nullable()->constrained()->onDelete('set null');
            $table->foreignIdFor(Kelas::class)->nullable()->constrained()->onDelete('set null');
            $table->foreignIdFor(Guru::class)->nullable()->constrained()->onDelete('set null');
            $table->foreignIdFor(Periode::class)->nullable()->constrained()->onDelete('cascade');
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
