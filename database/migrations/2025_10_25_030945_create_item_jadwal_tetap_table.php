<?php

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
        Schema::create('item_jadwal_tetap', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Periode::class)->constrained()->onDelete('cascade');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('nama_kegiatan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_jadwal_tetap');
    }
};
