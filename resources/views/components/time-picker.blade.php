<div
x-data="{
    livewireValue: @entangle($attributes->wire('model')),
    h: '00',
    m: '00',
    formatPart(val, max) {
        let num = parseInt(val.replace(/[^0-9]/g, '').slice(0, 2), 10);
        if (isNaN(num)) num = 0;
        if (num > max) num = max;
        return num.toString().padStart(2, '0');
    },
    init() {
        if (this.livewireValue && this.livewireValue.includes(':')) {
            [this.h, this.m] = this.livewireValue.split(':');
        }
        this.$watch('h', () => {
            this.livewireValue = `${this.h}:${this.m}`;
        });
        this.$watch('m', () => {
            this.livewireValue = `${this.h}:${this.m}`;
        });
        this.$watch('livewireValue', (val) => {
            if (val && val.includes(':')) {
                [this.h, this.m] = val.split(':');
            }
            this.livewireValue = `${this.h}:${this.m}`;
        });
    },
}"
x-modelable="livewireValue"
class="flex items-center w-full max-w-[300px] rounded-lg shadow-sm border border-gray-300 bg-white focus-within:ring-2 focus-within:ring-primary focus-within:border-primary transition duration-150"
{{ $attributes->except('wire:model') }}
>
    <input type="text"
           x-model="h"
           x-on:blur="h = formatPart(h, 23)"
           x-on:focus="$event.target.select()"
           maxlength="2"
           class="w-1/2 p-2.5 text-center text-gray-800 text-sm font-mono focus:outline-none bg-transparent"
           placeholder="HH">

    <span class="text-gray-500 text-lg font-bold">:</span>

    <input type="text"
           x-model="m"
           x-on:blur="m = formatPart(m, 59)"
           x-on:focus="$event.target.select()"
           maxlength="2"
           class="w-1/2 p-2.5 text-center text-gray-800 text-sm font-mono focus:outline-none bg-transparent"
           placeholder="MM">
</div>
