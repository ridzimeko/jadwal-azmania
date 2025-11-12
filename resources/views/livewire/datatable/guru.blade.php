<?php

use App\Models\Guru;
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
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component implements HasActions, HasSchemas, HasTable {
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    #[On('refreshGuruTable')]
    public function refresh()
    {
        $this->dispatch('$refresh');
    }

    public function table(Table $table): Table
    {
        return $table->query(function () {
            //filter tingkat
            $query = Guru::query()
                ->orderBy('id', 'desc');
            return $query;
        })
            ->searchable()
            ->columns([
                // Tambahkan kolom nomor urut paling awal
                TextColumn::make('index')->label('No')->rowIndex()->sortable(false)->searchable(false),
                TextColumn::make('kode_guru')->label('Kode Mapel')->searchable(true),
                TextColumn::make('nama_guru')->label('Jenis Mapel')->searchable(true),
                TextColumn::make('warna')->label('Warna')->formatStateUsing(
                    fn($state) => $state ? "<div class='flex items-center gap-2'><span class='inline-block w-6 h-6 rounded' style='background-color: {$state}'></span>{$state}</div>" : '-'
                )->html()->searchable(true),
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

                        Notification::make()->title('Data guru berhasil dihapus')->success()->send();
                        $this->dispatch('refreshGuruTable');
                    }),
            ])
            ->toolbarActions([
                BulkAction::make('deleteSelected')
                    ->label('Hapus Data yang Dipilih')
                    ->icon('heroicon-o-trash') // ikon trash 🗑️
                    ->color('danger')
                    ->modalHeading('Hapus Data')
                    ->modalDescription('Apakah anda yakin ingin menghapus data guru ini? Semua jadwal dengan guru ini AKAN DIHAPUS!')
                    ->requiresConfirmation() // muncul modal konfirmasi
                    ->action(function ($records) {
                        $records->each->delete(); // hapus semua data yang dipilih
                        $total = count($records);
                        Notification::make()
                            ->title($total ? "{$total} data guru berhasil dihapus!" : "data guru berhasil dihapus!")
                            ->success()
                            ->send();
                        $this->dispatch('refreshGuruTable');
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