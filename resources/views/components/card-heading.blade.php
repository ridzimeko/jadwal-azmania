@props([
    'title' => null,
    'description' => null,
    'action_buttons' => null
])

<header {{ $attributes->merge(['class' => "flex justify-between gap-4 flex-wrap"])}}>
    <div class="flex flex-col gap-2">
        <h2 class="text-3xl font-bold">{{ $title }}</h2>
        @if ($description)
            <p class="text-gray-400">{{ $description }}</p>
        @endif
    </div>

    <!-- Actions Button -->
    <div class="flex items-center gap-2 flex-wrap">
       {{ $action_buttons ?? '' }}
    </div>
</header>
