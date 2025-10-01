<?php
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

public function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('title'),
            TextColumn::make('slug'),
            IconColumn::make('is_featured')
                ->boolean(),
        ]);
}
?>

<div class="p-6 flex flex-col gap-6 w-full bg-white rounded-lg">
    <header class="flex justify-between gap-4">
        <div class="flex flex-col gap-2">
            <h2 class="text-3xl font-bold">{{ $title ?? 'Judul' }}</h2>
            <p class="text-gray-400">{{ $description ?? 'Deskripsi' }}</p>
        </div>

        <div>
            @if ($canImportExcel ?? true)
            <flux:button icon="document" class="!bg-green-700 !text-white">Import dari Excel</flux:button>
            @endif
            <flux:button icon="plus" class="!bg-primary !text-white">Tambah Data</flux:button>
            <flux:button icon="arrow-down-tray">Unduh Data</flux:button>
        </div>
    </header>

    <div class="flex justify-between bg-neutral-50 rounded-md w-full py-3 px-4">
        <flux:input icon="magnifying-glass" placeholder="Cari Data" class="max-w-[320px]" />
    </div>
</div>
