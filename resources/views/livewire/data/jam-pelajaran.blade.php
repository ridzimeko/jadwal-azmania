<?php

use App\Models\JamPelajaran;
use App\Rules\JamPelajaranBentrokRule;
use Filament\Notifications\Notification;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Title('Data Kelas')] class extends Component {
    protected $columnDefs = [['name' => 'Jam Ke', 'field' => 'urutan'], ['name' => 'Jam Mulai', 'field' => 'jam_mulai'], ['name' => 'Jam Selesai', 'field' => 'jam_selesai']];

    public ?array $formData = null;
    public array $jamBentrokList = [];
    public bool $isEdit = false;

    protected function rules(): array
    {
        return [
            'formData.urutan' => ['required', 'integer', 'max_digits:2',  Rule::unique('jam_pelajaran', 'urutan')->ignore($this->formData['id'] ?? null)],
            'formData.jam_mulai' => ['required', 'date_format:H:i'],
            'formData.jam_selesai' => 'required|date_format:H:i|after:formData.jam_mulai',
        ];
    }

    protected function messages(): array
    {
        return [
            'formData.urutan.required' => 'Urutan jam wajib diisi.',
            'formData.urutan.string' => 'Urutan jam harus berupa teks.',
            'formData.urutan.max_digits' => 'Urutan jam tidak boleh lebih dari :max digit.',
            'formData.urutan.unique' => 'Urutan jam sudah terdaftar',

            'formData.jam_mulai.required' => 'Jam mulai wajib diisi.',
            'formData.jam_mulai.date_format' => 'Format jam mulai harus HH:MM (24 jam).',

            'formData.jam_selesai.required' => 'Jam selesai wajib diisi.',
            'formData.jam_selesai.date_format' => 'Format jam selesai harus HH:MM (24 jam).',
            'formData.jam_selesai.after' => 'Jam selesai harus lebih besar dari jam mulai.',
        ];
    }

    public function validateJamBentrok()
    {
        $this->jamBentrokList = [];

        $exists = JamPelajaran::when($this->formData['id'], fn($q) => $q->where('id', '!=', $this->formData['id']))
            ->where(function ($q) {
                $q->where('jam_mulai', '<', $this->formData['jam_selesai'])
                    ->where('jam_selesai', '>', $this->formData['jam_mulai']);
            })
            ->get();

        if ($exists->count() > 0) {
            foreach ($exists as $jam) {
                $this->jamBentrokList[] = "Jam Ke-{$jam->urutan} ({$jam->jam_mulai} - {$jam->jam_selesai})";
            }
        }
    }

    #[On('openAddModal')]
    public function openAddModal()
    {
        $this->isEdit = false;
        $this->formData = [
            'urutan' => '',
            'jam_mulai' => '',
            'jam_selesai' => '',
        ];
        Flux::modal('jam-modal')->show();
    }

    #[On('openEditModal')]
    public function openEditModal($record)
    {
        $this->isEdit = true;
        $this->formData = $record;
        Flux::modal('jam-modal')->show();
    }

    public function save()
    {
        $this->validate();

        $this->validateJamBentrok();

        // Jika ada bentrok, jangan lanjut
        if (count($this->jamBentrokList) > 0) {
            return;
        }

        if ($this->isEdit) {
            JamPelajaran::find($this->formData['id'])->update([
                'urutan' => $this->formData['urutan'],
                'jam_mulai' => $this->formData['jam_mulai'],
                'jam_selesai' => $this->formData['jam_selesai'],
            ]);
        } else {
            JamPelajaran::create($this->formData);
        }

        Notification::make()->title('Data Jam Pelajaran Tersimpan')->success()->send();
        Flux::modal('jam-modal')->close();
        $this->dispatch('refreshTable');
    }
};
?>

<div class="dash-card">
    <x-card-heading title="Data Jam Pelajaran">
        <x-slot name="action_buttons">
            <flux:button wire:click="openAddModal" icon="plus" class="!bg-primary !text-white">Tambah Data
            </flux:button>
        </x-slot>
    </x-card-heading>

    {{-- Datatable --}}
    <livewire:datatable.index actionType="data" :columns="$this->columnDefs" :model="JamPelajaran::class" />

    {{-- Add Data Modal --}}
    <flux:modal name="jam-modal" class="w-[85%] md:w-[480px]">
        <form wire:submit.prevent="save">
            <div class="space-y-4">
                <div>
                    <flux:heading size="lg">
                        {{ $isEdit ? 'Ubah Data' : 'Tambah Data' }} Jam Pelajaran
                    </flux:heading>
                </div>

                @if (count($this->jamBentrokList) >= 1)
                <flux:callout variant="danger" icon="x-circle" heading="Terdapat jam pelajaran yang bentrok!">
                    <flux:callout.text>
                        <ul>
                            @foreach ($this->jamBentrokList as $jam)
                            <li>
                                <div>• {{ $jam }}</div>
                            </li>
                            @endforeach
                        </ul>
                    </flux:callout.text>
                </flux:callout>
                @endif

                <flux:input wire:model.defer="formData.urutan" label="Jam Ke" placeholder="Jam Ke" />

                <div class="flex flex-row items-center gap-6 w-full">
                    <flux:field class="min-w-[200px]">
                        <flux:label>Jam Mulai</flux:label>
                        <x-time-picker name="formData.jam_mulai" wire:model.defer="formData.jam_mulai" class="w-full" />
                        <flux:error name="formData.jam_mulai" />
                    </flux:field>

                    <flux:field class="min-w-[200px]">
                        <flux:label>Jam Selesai</flux:label>
                        <x-time-picker name="formData.jam_selesai" wire:model.defer="formData.jam_selesai" class="w-full" />
                        <flux:error name="formData.jam_selesai" />
                    </flux:field>
                </div>

                <div class="flex">
                    <flux:spacer />
                    <flux:button type="submit" variant="filled" class="!bg-primary !text-white">Simpan</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

    {{-- Import Excel Modal --}}
    <livewire:excel-import-modal context="kelas" />
</div>