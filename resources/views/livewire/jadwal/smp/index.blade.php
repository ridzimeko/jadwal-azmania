<?php

use App\Imports\GuruImport;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Livewire\Volt\Component;
use Maatwebsite\Excel\Facades\Excel;

new class extends Component {
    protected $columnDefs = [
        ['name' => 'No', 'field' => 'no'],
        ['name' => 'Kelas', 'field' => 'kelas'],
        ['name' => 'Hari', 'field' => 'hari'],
        ['name' => 'Jam Mulai', 'field' => 'jam_mulai'],
        ['name' => 'Jam Selesai', 'field' => 'jam_selesai'],
        ['name' => 'Mata Pelajaran', 'field' => 'mapel'],
        ['name' => 'Guru Pengajar', 'field' => 'guru'],
    ];

    protected function getHeaderActions(): array
{
    return [
        Action::make('importExcel')
            ->label('Import Excel')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('success')
            ->modalHeading('Import Data Guru')
            ->modalButton('Import')
            ->form([
                FileUpload::make('file')
                    ->label('Upload File Excel/CSV')
                    ->required()
                    ->acceptedFileTypes([
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'text/csv',
                    ]),
            ])
            ->action(function (array $data): void {
                Excel::import(new GuruImport, $data['file']->getRealPath());
                $this->notify('success', 'Data berhasil diimport!');
            }),
    ];
}

}
?>

<div class="dash-card">
    <x-card-heading title="Jadwal Pelajaran SMP" description="Manajemen jadwal Pelajaran untuk tingkat SMP">
        <x-slot name="action_buttons">
            <flux:button icon="document" class="!bg-az-green !text-white">Import dari Excel</flux:button>
            <flux:button icon="plus" class="!bg-primary !text-white">Tambah Data</flux:button>
            <flux:button icon="arrow-down-tray">Unduh Data</flux:button>
        </x-slot>
    </x-card-heading>

    <livewire:datatable.index :columns="$this->columnDefs" />
</div>
