@php
    $columnDefs = [
        ['name' => 'No', 'field' => 'no'],
        ['name' => 'Kelas', 'field' => 'kelas'],
        ['name' => 'Hari', 'field' => 'hari'],
        ['name' => 'Jam Mulai', 'field' => 'jam_mulai'],
        ['name' => 'Jam Selesai', 'field' => 'jam_selesai'],
        ['name' => 'Mata Pelajaran', 'field' => 'mapel'],
        ['name' => 'Guru Pengajar', 'field' => 'guru'],
    ];
@endphp


@livewire('datatable.index', [
    'title' => 'Jadwal Pelajaran SMP',
    'description' => 'Manajemen jadwal Pelajaran untuk tingkat SMP',
    'columns' => $columnDefs,
])
