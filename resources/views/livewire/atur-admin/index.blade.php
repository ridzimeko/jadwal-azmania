<?php

use Filament\Notifications\Notification;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
#[Title('Kelola Admin')]
class extends Component
{
    protected $columnDefs = [
        ['name' => 'Nama Akun', 'field' => 'nama'],
        ['name' => 'Username', 'field' => 'username'],
        ['name' => 'Role', 'field' => 'role'],
        ['name' => 'Terakhir Diubah', 'field' => 'updated_at'],
    ];

    public ?array $formData = [
        'nama' => '',
        'username' => '',
        'role' => '',
    ];

    public bool $isEdit = false;

    protected function rules(): array
    {
        $rules = [
            'formData.nama' => ['required', 'string', 'max:30'],
            'formData.username' => ['required', 'string', 'max:30', Rule::unique('users', 'username')->ignore($this->formData['id'] ?? null)],
            'formData.role' => ['required', 'string', 'max:30', 'in:admin,superadmin'],
        ];

        // Tambahkan validasi password hanya jika $isEdit == false
        if (!$this->isEdit) {
            $rules['formData.password'] = ['required', 'string', 'min:8'];
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'formData.nama.required' => 'Nama tidak boleh kosong.',
            'formData.nama.string' => 'Nama harus berupa teks.',
            'formData.nama.max' => 'Nama maksimal 30 karakter.',

            'formData.username.required' => 'Username wajib diisi.',
            'formData.username.string' => 'Username harus berupa teks.',
            'formData.username.max' => 'Username maksimal 30 karakter.',
            'formData.username.unique' => 'Username sudah digunakan, silakan pilih yang lain.',

            'formData.password.required' => 'password tidak boleh kosong.',
            'formData.password.string' => 'Password harus berupa teks.',
            'formData.password.min' => 'Password minimal 8 karakter.',

            'formData.role.required' => 'Role wajib dipilih.',
            'formData.role.string' => 'Role harus berupa teks.',
            'formData.role.in' => 'Role harus berupa admin atau superadmin.',
        ];
    }

    #[On('openAddModal')]
    public function openAddModal()
    {
        $this->isEdit = false;
        $this->formData = [
            'nama' => '',
            'username' => '',
            'role' => '',
        ];
        Flux::modal('admin-modal')->show();
    }

    #[On('openEditModal')]
    public function openEditModal($record)
    {
        $this->isEdit = true;
        $this->formData = $record;
        Flux::modal('admin-modal')->show();
    }

    public function save()
    {

        $this->validate();

        if ($this->isEdit) {
            \App\Models\User::find($this->formData['id'])->update($this->formData);
        } else {
            \App\Models\User::create($this->formData);
        }

        Notification::make()
            ->title('Data Akun Admin Tersimpan')
            ->success()
            ->send();
        Flux::modal('admin-modal')->close();
        $this->dispatch('refreshTable');
    }
};
?>

<div class="dash-card">
    <x-card-heading title="Data Akun Admin">
        <x-slot name="action_buttons">
            <flux:button wire:click="openAddModal" icon="plus" class="!bg-primary !text-white">Tambah Data
            </flux:button>
        </x-slot>
    </x-card-heading>

    {{-- Datatable --}}
    <livewire:datatable.index actionType="admin" :columns="$this->columnDefs" :model="\App\Models\User::class" />

    <livewire:atur-admin._change-password />

    {{-- Admin Modal --}}
    <flux:modal name="admin-modal" class="w-[85%] md:w-[520px]">
        <form wire:submit.prevent="save">
            <div class="space-y-3">
                <div>
                    <flux:heading size="lg">
                        {{ $isEdit ? 'Ubah Data Akun Admin' : 'Tambah Data Akun Admin' }}
                    </flux:heading>
                </div>
                <flux:input wire:model.defer="formData.nama" label="Nama Admin" placeholder="Nama Admin" />
                <flux:input wire:model.defer="formData.username" label="Username" placeholder="Username" />
                @if (!$this->isEdit)
                <flux:input wire:model.defer="formData.password" type="password" label="Password" placeholder="Password" />
                @endif

                <flux:field>
                    <flux:label>Role Admin</flux:label>

                    @php
                    $adminOptions = collect(['admin', 'superadmin'])
                    ->map(fn($hari) => ['label' => $hari, 'value' => $hari])
                    ->toArray();
                    @endphp

                    <x-select name="role" wire:model="formData.role" :search="false" :options="$adminOptions" placeholder="Pilih Role Admin" />
                    <flux:error name="formData.role" />
                </flux:field>

                <div class="flex mt-8">
                    <flux:spacer />
                    <flux:button type="submit" variant="filled" class="!bg-primary !text-white">Simpan</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>
</div>
