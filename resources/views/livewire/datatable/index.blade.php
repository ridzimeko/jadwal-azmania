
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
use Livewire\Volt\Component;

new class extends Component implements HasActions, HasSchemas, HasTable {
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public $title;
    public $description;
    public $action_buttons;
    public $query = null;
    public array $columns = [];

    public function table(Table $table): Table
    {
        return $table
            ->records(fn() => [
                ['no' => 'Budi', 'mapel' => 'Matematika'],
                ['no' => 'Siti', 'mapel' => 'Bahasa Inggris'],
            ])
            ->columns(collect($this->columns)->map(function ($col) {
                $column = TextColumn::make($col['field'])
                    ->label($col['name']);

                // Kalau ada config searchable
                if (!empty($col['searchable']) && $col['searchable'] === true) {
                    $column->searchable();
                }

                // Kalau ada config sortable
                if (!empty($col['sortable']) && $col['sortable'] === true) {
                    $column->sortable();
                }

                return $column;
            })->toArray())
            ->recordActions([
                Action::make('edit')
                    ->iconButton()
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->extraAttributes(['class' => 'bg-yellow-500 hover:bg-yellow-600 text-white !px-2 mr-1'])
                    ->url(fn($record) => route('jadwal.ma', $record)),

                Action::make('delete')
                    ->iconButton()
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->extraAttributes(['class' => 'bg-red-600 hover:bg-red-700 text-white !px-2'])
                    ->requiresConfirmation()
                    ->action(fn($record) => $record->delete()),
            ])
            ->toolbarActions([BulkAction::make('dummy')->label('Hapus Data yang Dipilih')->color('danger')->action(fn($records) => null)]);
    }
};

?>


<div>
    <div class="flex justify-between bg-neutral-50 rounded-md w-full py-3 px-4">
        <flux:input icon="magnifying-glass" placeholder="Cari Data" class="max-w-[320px]" />
    </div>

    {{ $this->table }}
</div>
