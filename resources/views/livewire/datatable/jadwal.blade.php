<?php

use App\Helpers\JadwalHelper;
use App\Models\JadwalPelajaran;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Volt\Component;

new class extends Component implements HasActions, HasSchemas, HasTable {
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    #[Reactive]
    public $periode_id;

    #[Reactive]
    public $tingkat;

    public $useEdit = true;
    public $useHariIni;

    public function mount($tingkat = null)
    {
        $this->tingkat = $tingkat;
    }

    #[On('refreshJadwalTable')]
    public function refresh()
    {
        $this->dispatch('$refresh');
    }

    public function table(Table $table): Table
    {
        $rTable = $table
            ->query(function () {
                //filter tingkat
                $query = JadwalHelper::getQuery($this->periode_id, $this->tingkat);

                // if ($this->useHariIni) {
                //     $currentDay = JadwalHelper::getCurrentDay();
                //     $query->where('hari', $currentDay);
                // }
                return $query;
            })
            ->recordClasses(fn(JadwalPelajaran $record) => $record->is_bentrok ? 'bg-red-100 text-red-700 font-semibold dark:bg-red-900/20' : '')
            ->searchable()
            ->columns([
                // Tambahkan kolom nomor urut paling awal
                TextColumn::make('index')->label('No')->rowIndex()->sortable(false)->searchable(false),
                // TextColumn::make('kategori')->label('Kategori')->searchable(true),
                TextColumn::make('kelas.nama_kelas')->label('Kelas')->searchable(true),
                TextColumn::make('hari')->label('Hari')->searchable(true),
                TextColumn::make('jamPelajaran.urutan')
                    ->label('Jam Ke')
                    ->searchable(true),
                TextColumn::make('jamPelajaran')
                    ->label('Jam')
                    ->formatStateUsing(fn($state, $record) => $record->jamPelajaran ? $record->jamPelajaran->jam_mulai . ' - ' . $record->jamPelajaran->jam_selesai : '-')
                    ->searchable(query: function ($query, $search) {
                        $query->whereHas('jamPelajaran', function ($q) use ($search) {
                            $q->where('jam_mulai', 'like', "%{$search}%")
                                ->orWhere('jam_selesai', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('mataPelajaran.nama_mapel')->label('Mata Pelajaran')->searchable(true),
                TextColumn::make('guru.nama_guru')->label('Guru Pengajar')->searchable(true)->default('-'),
            ])
            ->filters([
                SelectFilter::make('hari')->options([
                    'senin' => 'Senin',
                    'selasa' => 'Selasa',
                    'rabu' => 'Rabu',
                    'kamis' => 'Kamis',
                    'jumat' => 'Jumat',
                    'sabtu' => 'Sabtu',
                ]),
                SelectFilter::make('kelas.nama_kelas')->label('Kelas')->relationship('kelas', 'nama_kelas'),
            ])
            ->emptyStateHeading('Tidak ada data jadwal pelajaran');

        if ($this->useEdit) {
            $rTable
                ->toolbarActions([
                    BulkAction::make('deleteSelected')
                        ->label('Hapus Data yang Dipilih')
                        ->icon('heroicon-o-trash') // ikon trash 🗑️
                        ->color('danger')
                        ->modalHeading('Hapus Jadwal')
                        ->modalDescription('Apakah anda yakin ingin menghapus data ini?')
                        ->requiresConfirmation() // muncul modal konfirmasi
                        ->action(function ($records) {
                            $records->each->delete(); // hapus semua data yang dipilih
                            Notification::make()->title('Data berhasil dihapus!')->success()->send();
                            $this->dispatch('refreshJadwalTable');
                        })
                        ->deselectRecordsAfterCompletion(),
                ])
                ->recordActions([
                    Action::make('edit')
                        ->iconButton()
                        ->icon('heroicon-o-pencil')
                        ->color('warning')
                        ->extraAttributes(['class' => 'bg-yellow-500 hover:bg-yellow-600 text-white !px-2 mr-1'])
                        ->action(fn($record) => $this->dispatch('openEditJadwal', $record->toArray())),
                    Action::make('delete')
                        ->iconButton()
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->extraAttributes(['class' => 'bg-red-600 hover:bg-red-700 text-white !px-2'])
                        ->modalHeading('Hapus Jadwal')
                        ->modalDescription('Apakah anda yakin ingin menghapus data ini?')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->delete();
                            Notification::make()->title('Data berhasil dihapus!')->success()->send();
                            $this->dispatch('refreshJadwalTable');
                        }),
                ]);
        }
        return $rTable;
    }
};

?>

<div>
    {{ $this->table }}
</div>