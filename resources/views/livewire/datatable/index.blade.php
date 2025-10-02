<?php

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Table;
use Livewire\Volt\Component;
use Mockery\Undefined;

use function Livewire\Volt\layout;

layout('layouts/app');

new class extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public $title, $description, $canImportExcel;
    public $query = null, $columns = [];

    public function table(Table $table): Table
    {
        return $table
        ->records(fn () => [])
        ->columns($this->columns)
        ->recordActions([
            Action::make('edit')
            ->iconButton()
            ->icon('heroicon-o-pencil')
            ->color('warning')
            ->extraAttributes(['class' => 'bg-yellow-500 hover:bg-yellow-600 text-white !px-2 mr-1'])
            ->url(fn ($record) => route('jadwal.ma', $record)),

            Action::make('delete')
            ->iconButton()
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->extraAttributes(['class' => 'bg-red-600 hover:bg-red-700 text-white !px-2'])
            ->requiresConfirmation()
            ->action(fn ($record) => $record->delete()),
        ])
        ->bulkActions([
            BulkAction::make('dummy')
        ->label('Hapus Data yang Dipilih')
        ->color('danger')
        ->action(fn ($records) => null),
        ]);
    }
};

?>


<div class="p-6 flex flex-col gap-6 w-full bg-white rounded-lg">
    <header class="flex justify-between gap-4">
        <div class="flex flex-col gap-2">
            <h2 class="text-3xl font-bold">{{ $title ?? 'Judul' }}</h2>
            <p class="text-gray-400">{{ $description ?? 'Deskripsi' }}</p>
        </div>

        <div>
            @if ($canImportExcel ?? true)
            <flux:button icon="document" class="!bg-az-green !text-white">Import dari Excel</flux:button>
            @endif
            <flux:button icon="plus" class="!bg-primary !text-white">Tambah Data</flux:button>
            <flux:button icon="arrow-down-tray">Unduh Data</flux:button>
        </div>
    </header>

    <div class="flex justify-between bg-neutral-50 rounded-md w-full py-3 px-4">
        <flux:input icon="magnifying-glass" placeholder="Cari Data" class="max-w-[320px]" />
    </div>

    {{ $this->table }}
</div>
