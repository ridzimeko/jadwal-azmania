<x-layouts.app title="Beranda">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card title="Mata Pelajaran" :total="$totalMataPelajaran" icon="book-open" />
        <x-stat-card title="Kelas" :total="$totalKelas" icon="building-library" />
        <x-stat-card title="Guru" :total="$totalGuru" icon="graduation-hat" />
        <x-stat-card title="Admin" :total="$totalUsers" icon="users" />
    </div>

    <div class="flex flex-col gap-6 dash-card mt-6">
        <x-card-heading title="Jadwal Pelajaran Terbaru" :description="'Periode tahun ajaran ' . $periode->tahun_ajaran" class="mb-4" />

        {{-- Datatable --}}
        <livewire:datatable.jadwal :periode_id="$periode->id" :useEdit="false"  />
    </div>
</x-layouts.app>
