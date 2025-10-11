<?php

use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    protected $columnDefs = [['name' => 'Nama Akun', 'field' => 'nama'], ['name' => 'Username', 'field' => 'username'], ['name' => 'Role', 'field' => 'role']];

    public ?array $formData = null;
    public bool $isEdit = false;

    protected function rules(): array
    {
        return [
            'formData.nama' => ['required', 'string', 'max:30'],
            'formData.username' => ['required', 'string', 'max:30', Rule::unique('users', 'username')->ignore($this->formData['id'] ?? null)],
            'formData.password' => ['required', 'string', 'min:8'],
            'formData.role' => ['required', 'string', 'max:30', 'in:admin,superadmin'],
        ];
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

     #[On('openUpdatePasswordModal')]
     public function openUpdatePasswordModal($record)
    {
        $this->isEdit = true;
        $this->formData = $record;
        Flux::modal('update-admin-password-modal')->show();
    }

    public function save()
    {
        $this->validate();

        // dd($this->formData);

        if ($this->isEdit) {
            \App\Models\User::find($this->formData['id'])->update($this->formData);
        } else {
            \App\Models\User::create($this->formData);
        }

        Flux::modal('admin-modal')->close();
        $this->dispatch('refreshTable');
    }
};
?>

<div class="dash-card">
    <x-card-heading title="Data Akun Admin">
        <x-slot name="action_buttons">
            <flux:button @click="$wire.openAddModal" icon="plus" class="!bg-primary !text-white">Tambah Data
            </flux:button>
        </x-slot>
    </x-card-heading>

    {{-- Datatable --}}
    <livewire:datatable.index actionType="admin" :columns="$this->columnDefs" :model="\App\Models\User::class" />

    {{-- Admin Modal --}}
    <flux:modal name="admin-modal" class="md:w-[520px]">
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
                <flux:input wire:model.defer="formData.password" label="Password" placeholder="Password" />
                @endif

                <flux:field>
                <flux:label>Role Admin</flux:label>

                @php
                    $adminOptions = collect(['admin', 'superadmin'])
                        ->map(fn($hari) => ['label' => $hari, 'value' => $hari])
                        ->toArray();
                @endphp

                <x-select name="role" wireModel="formData.role" :search="false" :options="$adminOptions" placeholder="Pilih Role Admin" />
                <flux:error name="hari" />
            </flux:field>

                <div class="flex mt-8">
                    <flux:spacer />
                    <flux:button type="submit" variant="filled" class="!bg-primary !text-white">Simpan</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

     {{-- Update Admin Password Modal --}}
     <flux:modal name="update-admin-password-modal" class="md:w-[520px]">
        <form wire:submit.prevent="save">
            <div class="space-y-3">
                <div>
                    <flux:heading size="lg">
                        Ubah Kata Sandi Admin
                    </flux:heading>
                </div>
                <flux:input wire:model.defer="formData.password" type="password" label="Kata Sandi" placeholder="Kata Sandi" />
                <flux:input wire:model.defer="formData.confirmPassword" type="password" label="Konfirmasi Kata Sandi" placeholder="Ketik ulang kata sandi" />

                <div class="flex mt-8">
                    <flux:spacer />
                    <flux:button type="submit" variant="filled" class="!bg-primary !text-white">Simpan</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>
</div>
