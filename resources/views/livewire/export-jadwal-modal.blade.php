<?php

use App\Helpers\JadwalHelper;
use Livewire\Volt\Component;

new class extends Component {
    public $name = 'export-jadwal';
    public $periodeId;
    public ?array $formData = [
        'tingkat' => '',
        'filetype' => '',
    ];

    
    public function mount()
    {
        $this->formData['filetype'] = "pdf";
        $this->formData['tingkat'] = "smp";
    }

    protected function rules(): array
    {
        return [
            'formData.tingkat' => 'required|string|in:smp,ma',
            'formData.filetype' => 'required|in:pdf,excel',
        ];
    }

    protected function messages(): array
    {
        return [
            'formData.tingkat.*' => 'Pilih tingkat yang valid.',
            'formData.filetype.*' => 'Pilih format berkas yang valid.',
        ];
    }

    public function save()
    {
        $this->validate();

        $exportType = $this->formData['filetype'];
        $exportRoute = "export-jadwal.{$exportType}";
        $url = route($exportRoute, [
            'tingkat' => $this->formData['tingkat'],
            'periode' => $this->periodeId,
        ]);

        // Emit event ke browser
        $this->dispatch('export-file-jadwal', ['url' => $url]);
    }
}; ?>

<div>
    <flux:modal name="{{ $this->name }}" class="w-[90%] md:w-[32rem]">
        <flux:heading size="lg">Unduh Jadwal</flux:heading>
        <form wire:submit.prevent="save" class="mt-6">
            <div class="space-y-4">

                <flux:field>
                    <flux:label>Tingkat</flux:label>
                    <x-select wire:model="formData.tingkat" :search="false" :options="[['label' => 'SMP', 'value' => 'smp'], ['label' => 'MA', 'value' => 'ma']]" value="smp"
                        placeholder="Pilih tingkat" />
                    <flux:error name="formData.tingkat" />
                </flux:field>

                <flux:field>
                    <flux:label>Unduh Sebagai</flux:label>
                    <x-select wire:model="formData.filetype" :search="false" :options="[['label' => 'PDF', 'value' => 'pdf'], ['label' => 'Excel', 'value' => 'excel']]" value="pdf"
                        placeholder="Pilih format berkas" />
                    <flux:error name="formData.filetype" />
                </flux:field>
                <div class="flex gap-2 mt-8">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="filled"
                        class="!bg-primary !text-white disabled:text-gray-700 !disabled:bg-primary/40">Export Jadwal
                    </flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('export-file-jadwal', (event) => {
                const url = event[0].url;
                window.open(url, '_blank'); // buka tab baru
            });
        });
    </script>
</div>
