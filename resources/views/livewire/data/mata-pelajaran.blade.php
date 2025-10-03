@php
    $columnDefs = [
        ['name' => 'No', 'field' => 'no'],
        ['name' => 'Kode Mapel', 'field' => 'kode_mapel'],
        ['name' => 'Mata Pelajaran', 'field' => 'mapel'],
    ];
@endphp


@livewire('datatable.index', [
    'title' => 'Data Mata Pelajaran',
    'columns' => $columnDefs,
])
