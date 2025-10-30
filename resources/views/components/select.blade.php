@props([
    'options' => [], // array: [['value' => 1, 'label' => 'Budi'], ...]
    'placeholder' => 'Pilih item...',
    'searchPlaceholder' => 'Cari...',
    'search' => true,
    'clearable' => false,
])

<div
    x-data="{
        open: false,
        search: '',
        selectedLabel: '',
        value: @entangle($attributes->wire('model')),
        options: {{ json_encode($options) }},
        init() {
            this.setSelectedLabel();
            this.$watch('value', () => this.setSelectedLabel());
        },
        get filteredOptions() {
            if (this.search === '') return this.options;
            return this.options.filter(o =>
                o.label.toLowerCase().includes(this.search.toLowerCase())
            );
        },
        select(value, label) {
            this.value = value;
            this.selectedLabel = label;
            this.open = false;
        },
        clear() {
            this.value = '';
            this.selectedLabel = '';
        },
        setSelectedLabel() {
            const found = this.options.find(o => o.value == this.value);
            this.selectedLabel = found ? found.label : '';
        }
    }"
    x-modelable="value"
    {{ $attributes->merge(['class' => 'relative w-full']) }}
>
    <!-- Trigger -->
    <div class="relative">
        <button
            type="button"
            @click="open = !open"
            class="flex w-full justify-between items-center rounded-lg shadow-xs border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 py-2.5 px-4 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-primary"
        >
            <span x-text="selectedLabel || '{{ $placeholder }}'"></span>
            <div class="flex items-center gap-2">
                @if($clearable)
                <template x-if="selectedLabel">
                    <button
                        type="button"
                        @click.stop="clear()"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    >
                        <flux:icon name="x-mark" class="w-4 h-4" />
                    </button>
                </template>
                @endif
                <flux:icon name="chevron-down" class="w-4 h-4 text-gray-400" />
            </div>
        </button>
    </div>

    <!-- Dropdown -->
    <div
        x-cloak
        x-show="open"
        @click.away="open = false"
        x-transition
        class="absolute z-50 mt-2 w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-lg"
    >
        <!-- Search input -->
        @if($search)
        <div class="border-b border-gray-200 dark:border-gray-700">
            <input
                type="text"
                x-model="search"
                placeholder="{{ $searchPlaceholder }}"
                class="w-full p-2 rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-200 focus:ring-2 !focus:ring-primary !focus:border-primary"
            />
        </div>
        @endif

        <!-- Option list -->
        <ul class="max-h-48 overflow-y-auto py-1 text-sm text-gray-700 dark:text-gray-200">
            <template x-for="option in filteredOptions" :key="option.value">
                <li
                    @click="select(option.value, option.label)"
                    class="px-3 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800"
                    x-text="option.label"
                ></li>
            </template>

            <template x-if="filteredOptions.length === 0">
                <li class="px-3 py-2 text-gray-500 dark:text-gray-400 text-center text-sm">Tidak ditemukan</li>
            </template>
        </ul>
    </div>
</div>
