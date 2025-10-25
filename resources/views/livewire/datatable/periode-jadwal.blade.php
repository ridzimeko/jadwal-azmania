<?php

use App\Models\Periode;
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

    #[On('refreshPeriodeTable')]
    public function refresh()
    {
        $this->dispatch('$refresh');
    }

    public function table(Table $table): Table
    {
        return $table->query(function () {
            //filter tingkat
            $query = Periode::query()
                ->orderBy('created_at', 'desc');
            return $query;
        })
            ->searchable()
            ->columns([
                // Tambahkan kolom nomor urut paling awal
                TextColumn::make('index')->label('No')->rowIndex()->sortable(false)->searchable(false),
                TextColumn::make('tahun_ajaran')->label('Tahun Ajaran')->searchable(true),
                TextColumn::make('semester')->label('Semester')->searchable(true),
                TextColumn::make('aktif')->label('Status')
                ->formatStateUsing(function ($state, $record) {
                    $status = $record->aktif ?'Aktif' : 'Nonaktif';
                    return $status;
                })
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    '1' => 'success',
                    '0' => 'danger',
                })
                ->searchable(true),
            ])
            ->recordActions([
                Action::make('edit')
                    ->iconButton()
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->extraAttributes(['class' => 'bg-yellow-500 hover:bg-yellow-600 text-white !px-2 mr-1'])
                    ->action(fn($record) => $this->dispatch('openEditPeriode', $record->toArray())),
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
                        Notification::make()
                            ->title('Data berhasil dihapus!')
                            ->success()
                            ->send();
                        $this->dispatch('refreshPeriodeTable');
                    }),
            ])
            ->toolbarActions([
                BulkAction::make('deleteSelected')
                    ->label('Hapus Data yang Dipilih')
                    ->icon('heroicon-o-trash') // ikon trash 🗑️
                    ->color('danger')
                    ->modalHeading('Hapus Data')
                    ->modalDescription('Apakah anda yakin ingin menghapus periode ini? Semua jadwal dengan periode ini AKAN DIHAPUS!')
                    ->requiresConfirmation() // muncul modal konfirmasi
                    ->action(function ($records) {
                        $records->each->delete(); // hapus semua data yang dipilih
                        $total = count($records);
                        Notification::make()
                        ->title($total ? "{$total} Periode berhasil dihapus!" : "Periode berhasil dihapus!")
                        ->success()
                        ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
            ])
            ->emptyStateHeading('Tidak ada data periode');
    }
};

?>

<div>
    {{ $this->table }}
</div>
