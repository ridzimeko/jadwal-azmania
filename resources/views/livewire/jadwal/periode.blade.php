<?php

use App\Models\Periode;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
    #[Title('Periode Jadwal')]
    class extends Component implements HasActions, HasSchemas {
        use InteractsWithActions;
        use InteractsWithSchemas;

        public ?array $formData = [
            'tahun_ajaran' => '',
            'semester' => '',
            'aktif' => '',
        ];
        public bool $isEdit = false;
        public $periodeList;

        public function mount()
        {
            $this->periodeList = Periode::all();
        }

        protected function rules(): array
        {
            return [
                'formData.tahun_ajaran' => 'required|string',
                'formData.semester' => 'required|string',
            ];
        }

        protected function messages(): array
        {
            return [
                'formData.tahun_ajaran.required' => 'Tahun Ajaran wajib diisi.',
                'formData.tahun_ajaran.string' => 'Tahun Ajaran harus berupa teks.',

                'formData.semester.required' => 'Semester wajib diisi.',
                'formData.semester.string' => 'Semester harus berupa teks.',
            ];
        }

        public function openAddPeriode()
        {
            $this->isEdit = false;
            $this->formData = [
                'tahun_ajaran' => '',
                'semester' => '',
                'aktif' => '',
            ];
            Flux::modal('periode-modal')->show();
        }

        #[On('openEditPeriode')]
        public function openEditPeriode($record)
        {
            if ($record['id'] ?? null) {
                $this->isEdit = true;
            } else {
                $this->isEdit = false;
            }
            $this->formData = $record;
            Flux::modal('periode-modal')->show();
        }

        public function save()
        {
            $this->validate();

            if ($this->isEdit) {
                Periode::find($this->formData['id'])->update($this->formData);
            } else {
                Periode::create($this->formData);
            }

            Notification::make()->title('Periode Berhasil Tersimpan')->success()->send();
            Flux::modal('periode-modal')->close();
            $this->dispatch('refreshPeriodeTable');
        }

        public function deleteAction(): Action
        {
            return Action::make('delete')
                ->label('Hapus')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Hapus Periode')
                ->modalDescription('Apakah anda yakin ingin menghapus data ini?')
                ->action(function (array $arguments) {
                    $post = Periode::find($arguments['periode']);

                    $post?->delete();

                    Notification::make()->title('Data periode berhasil dihapus')->success()->send();
                    Flux::modal('periode-modal')->close();
                    $this->dispatch('refreshPeriodeTable');
                });
        }
    };
?>

<div class="dash-card">
    <x-card-heading title="Periode Jadwal"
        description="Manajemen periode jadwal pelajaran">
        <x-slot name="action_buttons">
            <flux:button icon="plus" @click="$wire.openAddPeriode" class="!bg-primary !text-white">
                Tambah Data
            </flux:button>
        </x-slot>
    </x-card-heading>

    <livewire:datatable.periode-jadwal />

    <x-filament-actions::modals />

    {{-- Add Data Modal --}}
    <flux:modal name="periode-modal" class="md:w-[480px] z-[30]">
        <form wire:submit.prevent="save" class="flex flex-col gap-4 max-w-[768px]">
            <flux:heading size="lg">
                {{ $isEdit ? 'Ubah Data' : 'Tambah Data' }} Periode
            </flux:heading>

            <flux:input wire:model.defer="formData.tahun_ajaran" label="Tahun Ajaran" placeholder="Tahun Ajaran" />

            <flux:field>
                <flux:label>Semester</flux:label>
                <x-select
                    wire:model="formData.semester"
                    :search="false"
                    :options="[
                    ['label' => 'Ganjil', 'value' => 'ganjil'],
                    ['label' => 'Genap', 'value' => 'genap'],
                ]"
                    placeholder="Pilih Semester" />
                <flux:error name="formData.semester" />
            </flux:field>

            <flux:field>
                <flux:label>Status</flux:label>
                <x-select
                    wire:model="formData.aktif"
                    :search="false"
                    :options="[
                    ['label' => 'Aktif', 'value' => '1'],
                    ['label' => 'Nonaktif', 'value' => '0'],
                ]"
                    placeholder="Pilih Semester" />
                <flux:error name="formData.semester" />
            </flux:field>

            <div class="flex mt-8">
                <flux:spacer />
                <flux:button type="submit" variant="filled" class="!bg-primary !text-white">Simpan</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
