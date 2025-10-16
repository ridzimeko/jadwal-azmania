<?php

use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public ?array $formData = [
        'nama' => '',
        'password' => '',
        'password_confirm' => ''
    ];

    public bool $isSuperadmin = false;
    protected $authUser = null;

    public function mount() // Di Livewire, gunakan mount() sebagai constructor
    {
        // Pastikan ada user yang login sebelum mengakses propertinya
        if (Auth::check()) {
            $this->isSuperadmin = Auth::user()->role === 'superadmin';
            $this->authUser = Auth::user();
        } else {
            $this->isSuperadmin = false; // Beri nilai default jika tidak ada user login
        }

        // isi formData
        $this->formData['id'] = Auth::user()->id;
        $this->formData['nama'] = Auth::user()->nama;
    }

    protected function rules(): array
    {
        $rules = [
            'formData.nama' => ['required', 'string', 'max:30'],
            'formData.password' => ['string', 'min:8'],
            'formData.password_confirm' => ['string', 'min:8', 'confirmed:formData.password'],
        ];

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'formData.nama.required' => 'Nama tidak boleh kosong.',
            'formData.nama.string' => 'Nama harus berupa teks.',
            'formData.nama.max' => 'Nama maksimal 30 karakter.',

            'formData.password*.required' => 'password tidak boleh kosong.',
            'formData.password*.string' => 'Password harus berupa teks.',
            'formData.password*.min' => 'Password minimal 8 karakter.',
            'formData.password*.confirmed' => 'Password minimal 8 karakter.',

            'formData.role.required' => 'Role wajib dipilih.',
            'formData.role.string' => 'Role harus berupa teks.',
            'formData.role.in' => 'Role harus berupa admin atau superadmin.',
        ];
    }

    public function save() {
        $this->validate();

        $form = [
            'nama' => $this->formData['nama'],
        ];

        if (!empty($this->formData['password'])) {
            $form['password'] = $this->formData['password'];
        }

        // update user
        User::find($this->formData['id'])->update($form);

        Notification::make()
        ->title('Pengaturan berhasil disimpan!')
        ->success()
        ->send();
    }
}; ?>

<div class="dash-card">
    <x-card-heading title="Pengaturan Akun" />

    <form wire:submit="save" class="max-w-[560px] flex flex-col gap-4">
        <flux:input wire:model.defer="formData.nama" label="Nama" placeholder="Nama" />
        <flux:input wire:model.defer="formData.password" type="password" label="Password" placeholder="***********" />
        <flux:input wire:model.defer="formData.password_confirm" type="password" label="Konfirmasi Password" placeholder="***********" />
        <flux:button type="submit" variant="filled" class="!bg-primary !text-white !mt-6 !w-fit">Simpan</flux:button>
    </form>
</div>
