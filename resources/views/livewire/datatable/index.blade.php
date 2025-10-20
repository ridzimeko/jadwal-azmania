<?php

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

    public $model;
    public $actionType;
    public array $columns = [];
    public string $scope = '';

    #[On('refreshTable')]
    public function refresh()
    {
        $this->dispatch('$refresh');
    }

    public function table(Table $table): Table
    {
        $defaultActions = [
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
                    $this->dispatch('refreshTable');
                }),
        ];

        $editActions = match ($this->actionType) {
            'data' => [
                Action::make('edit')
                    ->iconButton()
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->extraAttributes(['class' => 'bg-yellow-500 hover:bg-yellow-600 text-white !px-2 mr-1'])
                    ->action(fn($record) => $this->dispatch('openEditModal', $record->toArray())),
            ],
            'admin' => [
                Action::make('updatePassword')
                    ->iconButton()
                    ->icon('heroicon-o-key')
                    ->color('info')
                    ->extraAttributes(['class' => 'bg-blue-600 hover:bg-blue-700 text-white !px-2 mr-1'])
                    ->action(fn($record) => $this->dispatch('openUpdatePasswordModal', $record->toArray())),

                Action::make('editData')
                    ->iconButton()
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->extraAttributes(['class' => 'bg-yellow-500 hover:bg-yellow-600 text-white !px-2 mr-1'])
                    ->action(fn($record) => $this->dispatch('openEditModal', $record->toArray())),
            ],
            default => [],
        };

        $mTable = $table
            ->query(function () {
                $query = $this->model::query()->orderByDesc('id');

                if (property_exists($this, 'scope') && $this->scope && method_exists($this->model, 'scope' . ucfirst($this->scope))) {
                    $scope = $this->scope;
                    $query->$scope();
                }

                return $query;
            })
            ->searchable()
            ->columns(
                collect([
                    // Tambahkan kolom nomor urut paling awal
                    TextColumn::make('index')->label('No')->rowIndex()->sortable(false)->searchable(false),
                ])
                    ->merge(
                        collect($this->columns)->map(function ($col) {
                            $column = TextColumn::make($col['field'])->label($col['name']);

                            if (!empty($col['searchable']) && $col['searchable'] === false) {
                                $column->searchable(false);
                            } else {
                                $column->searchable();
                            }

                            if (!empty($col['sortable']) && $col['sortable'] === true) {
                                $column->sortable();
                            }

                            // 🔥 Format readable khusus untuk updated_at
                            if ($col['field'] === 'updated_at') {
                                $column->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d F Y H:i'));
                            }

                            return $column;
                        }),
                    )
                    ->toArray(),
            )
            ->toolbarActions([
                BulkAction::make('deleteSelected')
                    ->label('Hapus Data yang Dipilih')
                    ->icon('heroicon-o-trash') // ikon trash 🗑️
                    ->color('danger')
                    ->modalHeading('Hapus Data')
                    ->modalDescription('Apakah anda yakin ingin menghapus data ini?')
                    ->requiresConfirmation() // muncul modal konfirmasi
                    ->action(function ($records) {
                        $records->each->delete(); // hapus semua data yang dipilih
                        $this->dispatch('notify', message: 'Data berhasil dihapus!');
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);

        if ($this->actionType) {
            $mTable->recordActions(array_merge($editActions, $defaultActions));
        }

        return $mTable;
    }
};

?>


<div>
    <!-- <div class="flex justify-between bg-neutral-50 rounded-md w-full py-3 px-4">
        <flux:input icon="magnifying-glass" placeholder="Cari Data" class="max-w-[320px]" />
    </div> -->

    {{ $this->table }}
</div>
