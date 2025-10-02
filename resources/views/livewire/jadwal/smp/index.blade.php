@php
    use Filament\Tables\Columns\TextColumn;
@endphp

<div>
    @livewire('datatable.index', [
        'title'=> "Jadwal Pelajaran SMP",
        'description' => "Manajemen jadwal Pelajaran untuk tingkat MA",
        'columns' => [
            TextColumn::make('no')->label('No'),
            TextColumn::make('class')->label('Kelas'),
            TextColumn::make('day')->label('Hari'),
            TextColumn::make('hour_start')->label('Jam Mulai'),
            TextColumn::make('hour_end')->label('Jam Selesai'),
            TextColumn::make('lesson')->label('Mata Pelajaran'),
            TextColumn::make('teacher')->label('Guru Pengajar'),
        ]
    ])
</div>
