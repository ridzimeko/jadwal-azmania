<?php

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
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
    public array $columns = [];

    #[On('refreshTable')]
    public function refresh()
    {
        $this->dispatch('$refresh');
    }

    public function table(Table $table): Table
    {
        $actions = [];
        $defaultActions = [
            Action::make('edit')
                ->iconButton()
                ->icon('heroicon-o-pencil')
                ->color('warning')
                ->extraAttributes([
                    'class' => 'bg-yellow-500 hover:bg-yellow-600 text-white !px-2 mr-1',
                ])
                ->action(function ($record) {
                    $this->dispatch('openEditModal', $record->toArray());
                }),
            Action::make('delete')
                ->iconButton()
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->extraAttributes(['class' => 'bg-red-600 hover:bg-red-700 text-white !px-2'])
                ->requiresConfirmation()
                ->action(fn($record) => $record->delete()),
        ];

        // table actions
        if (request()->routeIs('data.*')) {
            $actions = [
                Action::make('edit')
                    ->iconButton()
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->extraAttributes([
                        'class' => 'bg-yellow-500 hover:bg-yellow-600 text-white !px-2 mr-1',
                    ])
                    ->action(function ($record) {
                        $this->dispatch('openEditModal', $record->toArray());
                    }),
            ];
        } elseif (request()->routeIs('jadwal.*')) {
            $actions = [
                Action::make('edit')
                    ->iconButton()
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->extraAttributes(['class' => 'bg-yellow-500 hover:bg-yellow-600 text-white !px-2 mr-1'])
                    ->url(
                        fn($record) => route('jadwal.edit', [
                            'tingkat' => request()->route('tingkat'),
                            'id_jadwal' => $record->id,
                        ]),
                    ),
            ];
        }

        return $table
            ->query(fn() => $this->model::query()->orderByDesc('id'))
            ->columns(
                collect([
                    // Tambahkan kolom nomor urut paling awal
                    TextColumn::make('index')->label('No')->rowIndex()->sortable(false)->searchable(false),
                ])
                    ->merge(
                        collect($this->columns)->map(function ($col) {
                            $column = TextColumn::make($col['field'])->label($col['name']);

                            if (!empty($col['searchable']) && $col['searchable'] === true) {
                                $column->searchable();
                            }

                            if (!empty($col['sortable']) && $col['sortable'] === true) {
                                $column->sortable();
                            }

                            return $column;
                        }),
                    )
                    ->toArray(),
            )
            ->recordActions(array_merge($defaultActions))
            ->toolbarActions([
                BulkAction::make('deleteSelected')
                    ->label('Hapus Data yang Dipilih')
                    ->icon('heroicon-o-trash') // ikon trash 🗑️
                    ->color('danger')
                    ->requiresConfirmation() // muncul modal konfirmasi
                    ->action(function ($records) {
                        $records->each->delete(); // hapus semua data yang dipilih
                        $this->dispatch('notify', message: 'Data berhasil dihapus!');
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }
};

?>


<div>
    <div class="flex justify-between bg-neutral-50 rounded-md w-full py-3 px-4">
        <flux:input icon="magnifying-glass" placeholder="Cari Data" class="max-w-[320px]" />
    </div>

    {{ $this->table }}
</div>
