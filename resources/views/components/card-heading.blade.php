@props([
    'title' => null,
    'description' => null,
    'action_buttons' => null
])

<header class="flex justify-between gap-4">
    <div class="flex flex-col gap-2">
        <h2 class="text-3xl font-bold">{{ $title }}</h2>
        @if ($description)
            <p class="text-gray-400">{{ $description }}</p>
        @endif
    </div>

    <!-- Actions Button -->
    <div>
       {{ $action_buttons ?? '' }}
    </div>
</header>
