<?php

use App\Models\MataPelajaran;
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
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component implements HasActions, HasSchemas, HasTable {
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    #[On('refreshMapelTable')]
    public function refresh()
    {
        $this->dispatch('$refresh');
    }

    public function table(Table $table): Table
    {
        return $table->query(function () {
            //filter tingkat
            $query = MataPelajaran::query()
                ->orderBy('id', 'desc');
            return $query;
        })
            ->searchable()
            ->columns([
                // Tambahkan kolom nomor urut paling awal
                TextColumn::make('index')->label('No')->rowIndex()->sortable(false)->searchable(false),
                TextColumn::make('kode_mapel')->label('Kode Mapel')->searchable(true),
                TextColumn::make('jenis_mapel')->label('Jenis Mapel')->searchable(true),
                TextColumn::make('jp_per_pekan')->label('Jatah Per Pekan')->searchable(true),
                TextColumn::make('nama_mapel')->label('Mata Pelajaran')->searchable(true),
            ])
            ->recordActions([
                Action::make('edit')
                    ->iconButton()
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->extraAttributes(['class' => 'bg-yellow-500 hover:bg-yellow-600 text-white !px-2 mr-1'])
                    ->action(fn($record) => $this->dispatch('openEditModal', $record->toArray())),
                Action::make('delete')
                    ->iconButton()
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->extraAttributes(['class' => 'bg-red-600 hover:bg-red-700 text-white !px-2'])
                    ->modalHeading('Hapus Data')
                    ->modalDescription('Apakah anda yakin ingin menghapus data ini?')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->delete();
                        Cache::forget('periode_options');

                        Notification::make()->title('Data mata pelajaran berhasil dihapus')->success()->send();
                        $this->dispatch('refreshMapelTable');
                    }),
            ])
            ->toolbarActions([
                BulkAction::make('deleteSelected')
                    ->label('Hapus Data yang Dipilih')
                    ->icon('heroicon-o-trash') // ikon trash 🗑️
                    ->color('danger')
                    ->modalHeading('Hapus Data')
                    ->modalDescription('Apakah anda yakin ingin menghapus mapel ini? Semua jadwal dengan mapel ini AKAN DIHAPUS!')
                    ->requiresConfirmation() // muncul modal konfirmasi
                    ->action(function ($records) {
                        $records->each->delete(); // hapus semua data yang dipilih
                        $total = count($records);
                        Notification::make()
                            ->title($total ? "{$total} Mata pelajaran berhasil dihapus!" : "Mata pelajaran berhasil dihapus!")
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
            ])
            ->emptyStateHeading('Tidak ada data mata pelajaran');
    }
};

?>

<div>
    {{ $this->table }}
</div>