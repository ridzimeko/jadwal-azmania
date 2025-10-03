@php
    $columnDefs = [
        ['name' => 'No', 'field' => 'no'],
        ['name' => 'NIP', 'field' => 'nip'],
        ['name' => 'Nama Guru', 'field' => 'guru'],
    ];
@endphp


@livewire('datatable.index', [
    'title' => 'Data Guru',
    'columns' => $columnDefs,
])
