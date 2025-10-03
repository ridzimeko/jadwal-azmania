@php
    $columnDefs = [
        ['name' => 'No', 'field' => 'no'],
        ['name' => 'Kode Kelas', 'field' => 'kode_kelas'],
        ['name' => 'Kelas', 'field' => 'kelas'],
    ];
@endphp


@livewire('datatable.index', [
    'title' => 'Data Kelas',
    'columns' => $columnDefs,
])
