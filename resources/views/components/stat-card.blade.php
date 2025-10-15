@props([
    'title' => 'string',
    'total' => 'string',
    'icon' => 'string',
])

<div class="flex items-center justify-between gap-4 rounded-md bg-white p-6">
    <div>
        <h2 class="text-gray-500 font-bold">{{ $title }}</h2>
        <h3 class="text-2xl font-semibold">{{ $total }}</h3>
    </div>
    <flux:icon name="{{ $icon }}" class="size-16" />
</div>
