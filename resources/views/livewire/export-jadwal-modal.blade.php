<?php

use Livewire\Volt\Component;

new class extends Component {
    public $name = 'export-jadwal';
    public $periode = '';
    public ?array $formData = [
        'filename' => '',
        'tingkat' => '',
        'filetype' => '',
    ];

    public function mount() {
        $tingkat = strtoupper($this->formData['tingkat']);
        $this->formData['filename'] = "Jadwal Pelajaran {$tingkat}-{$this->periode}";
        // $this->formData['filetype'] = 'pdf';
        // $this->formData['filetype'] = 'smp';
    }

    public function save() {
        $exportType = $this->formData['filetype'];
        $url = route("export-jadwal.{$exportType}", ['tingkat' => $this->tingkat]);

        // kirim event ke browser
        $this->dispatch('open-new-tab', ['url' => $url]);
    }
}; ?>

<div>
    <flux:modal name="{{ $name }}" class="min-w-[28rem]">
        <flux:heading size="lg">Unduh Jadwal</flux:heading>
        <form wire:submit.prevent="save" class="mt-6">
            <div class="space-y-4">

            <flux:input wire:model.defer="formData.filename" label="Nama Berkas" placeholder="Nama berkas..." />

            <flux:field>
                    <flux:label>Tingkat</flux:label>
                    <x-select
                        wire:model="formData.filetype"
                        :search="false"
                        :options="[
                            ['label' => 'SMP', 'value' => 'smp'],
                            ['label' => 'MA', 'value' => 'ma'],
                        ]"
                        value="smp"
                        placeholder="Pilih tingkat" />
                    <flux:error name="formData.filetype" />
                </flux:field>

                <flux:field>
                    <flux:label>Unduh Sebagai</flux:label>
                    <x-select
                        wire:model="formData.filetype"
                        :search="false"
                        :options="[
                            ['label' => 'PDF', 'value' => 'pdf'],
                            ['label' => 'Excel', 'value' => 'excel'],
                        ]"
                        value="pdf"
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
</div>
