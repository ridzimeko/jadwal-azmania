<?php

use App\Models\Periode;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Periode Jadwal')]
    class extends Component implements HasActions, HasSchemas {
    use InteractsWithActions;
    use InteractsWithSchemas;

    public ?array $formData = [
        'tahun_ajaran' => '',
        'semester' => '',
        'aktif' => false,
    ];
    public bool $isEdit = false;

    public function mount()
    {
        $activePeriode = Periode::where('aktif', true)->first() ?? Periode::latest()->first();

        // Automatic redirect to active period detail page unless user wants to view period list (?list=1)
        if ($activePeriode && !request()->has('list')) {
            return redirect()->route('jadwal.index', ['periode_id' => $activePeriode->id]);
        }
    }

    protected function rules(): array
    {
        $semester = ucfirst($this->formData['semester'] ?? '');

        return [
            'formData.tahun_ajaran' => [
                'required',
                'string',
                Rule::unique('periode', 'tahun_ajaran')
                    ->where('semester', $semester)
                    ->ignore($this->formData['id'] ?? null),
            ],
            'formData.semester' => 'required|string',
            'formData.aktif' => 'nullable|boolean',
        ];
    }

    protected function messages(): array
    {
        return [
            'formData.tahun_ajaran.required' => 'Tahun Ajaran wajib diisi.',
            'formData.tahun_ajaran.string' => 'Tahun Ajaran harus berupa teks.',
            'formData.tahun_ajaran.unique' => 'Kombinasi Tahun Ajaran dan Semester ini sudah terdaftar.',

            'formData.semester.required' => 'Semester wajib diisi.',
            'formData.semester.string' => 'Semester harus berupa teks.',
        ];
    }

    public function openAddPeriode()
    {
        $this->isEdit = false;
        $this->formData = [
            'tahun_ajaran' => '',
            'semester' => 'Ganjil',
            'aktif' => false,
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

    public function setActivePeriode($id)
    {
        Periode::query()->update(['aktif' => false]);
        Periode::where('id', $id)->update(['aktif' => true]);

        $periode = Periode::find($id);
        Notification::make()
            ->title("Periode {$periode?->tahun_ajaran} ({$periode?->semester}) Berhasil Ditetapkan Sebagai Periode Aktif!")
            ->success()
            ->send();
    }

    public function save()
    {
        $this->validate();

        $isAktif = !empty($this->formData['aktif']);

        if ($isAktif) {
            Periode::query()->update(['aktif' => false]);
        }

        if ($this->isEdit) {
            Periode::find($this->formData['id'])->update([
                'tahun_ajaran' => $this->formData['tahun_ajaran'],
                'semester' => ucfirst($this->formData['semester'] ?? 'Ganjil'),
                'aktif' => $isAktif,
            ]);
        } else {
            Periode::create([
                'tahun_ajaran' => $this->formData['tahun_ajaran'],
                'semester' => ucfirst($this->formData['semester'] ?? 'Ganjil'),
                'aktif' => $isAktif,
            ]);
        }

        Notification::make()->title('Periode Berhasil Tersimpan')->success()->send();
        Flux::modal('periode-modal')->close();
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
                $this->dispatch('$refresh');
            });
    }

    public function getPeriode()
    {
        return Periode::orderBy('aktif', 'desc')->orderBy('tahun_ajaran', 'desc')->get();
    }
};
?>

<div class="dash-card">
    <x-card-heading title="Kelola Periode Jadwal" description="Kelola periode tahun ajaran dan tentukan periode yang sedang aktif utama">
        <x-slot name="action_buttons">
            <flux:button icon="plus" wire:click="openAddPeriode" class="!bg-primary !text-white">
                Tambah Periode Baru
            </flux:button>
        </x-slot>
    </x-card-heading>

    <!-- main content -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
        @php
            $periodeList = $this->getPeriode();
        @endphp

        @foreach ($periodeList as $periode)
            <div class="relative bg-white dark:bg-gray-900 border rounded-2xl p-5 shadow-xs transition hover:shadow-md flex flex-col justify-between gap-4 {{ $periode->aktif ? 'border-emerald-500 ring-2 ring-emerald-500/20 bg-emerald-50/20 dark:bg-emerald-950/20' : 'border-gray-200 dark:border-gray-800' }}">
                <div class="space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <flux:heading class="font-extrabold text-lg text-gray-900 dark:text-white">{{ $periode->tahun_ajaran }}</flux:heading>
                        @if($periode->aktif)
                            <span class="inline-flex items-center gap-1 text-[11px] font-extrabold px-2.5 py-0.5 rounded-full bg-emerald-600 text-white shadow-xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                <span>PERIODE AKTIF</span>
                            </span>
                        @else
                            <span class="text-[11px] font-semibold text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-full">
                                Nonaktif
                            </span>
                        @endif
                    </div>
                    <flux:text class="text-sm font-semibold text-gray-600 dark:text-gray-400">Semester {{ $periode->semester }}</flux:text>
                </div>

                <div class="flex items-center justify-between gap-2 pt-3 border-t border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        @if(!$periode->aktif)
                            <flux:button wire:click="setActivePeriode({{ $periode->id }})" size="xs" variant="outline" icon="check" class="!text-emerald-700 dark:!text-emerald-300 border-emerald-300">
                                Set Aktif
                            </flux:button>
                        @endif
                        <flux:button wire:click="openEditPeriode({{ json_encode($periode) }})" size="xs" variant="ghost" icon="pencil">
                            Edit
                        </flux:button>
                    </div>

                    <a href="{{ route('jadwal.index', ['periode_id' => $periode->id]) }}" 
                       class="inline-flex items-center gap-1 text-xs font-extrabold text-primary hover:underline">
                        <span>Lihat Jadwal</span>
                        <flux:icon name="chevron-right" class="w-3.5 h-3.5 text-primary" />
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Add/Edit Periode Modal --}}
    <flux:modal name="periode-modal" class="md:w-[480px] z-[30]">
        <form wire:submit.prevent="save" class="flex flex-col gap-4 max-w-[768px]">
            <flux:heading size="lg">
                {{ $isEdit ? 'Ubah Data' : 'Tambah Data' }} Periode
            </flux:heading>

            <flux:input wire:model.defer="formData.tahun_ajaran" label="Tahun Ajaran" placeholder="Contoh: 2025/2026" />

            <flux:field>
                <flux:label>Semester</flux:label>
                <x-select wire:model="formData.semester" :search="false"
                    :options="[['label' => 'Ganjil', 'value' => 'Ganjil'], ['label' => 'Genap', 'value' => 'Genap']]"
                    placeholder="Pilih Semester" />
                <flux:error name="formData.semester" />
            </flux:field>

            <flux:field class="flex items-center gap-2 pt-2">
                <input type="checkbox" wire:model="formData.aktif" id="periode_aktif_checkbox" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                <flux:label for="periode_aktif_checkbox" class="cursor-pointer font-bold text-xs text-gray-800 dark:text-gray-200">
                    Jadikan Sebagai Periode Aktif Utama
                </flux:label>
            </flux:field>

            <div class="flex mt-6 gap-2 justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="filled" class="!bg-primary !text-white">Simpan</flux:button>
            </div>
        </form>
    </flux:modal>
</div>