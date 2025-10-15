<?php

use Filament\Notifications\Notification;
use Flux\Flux;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component
{
    public ?array $formData = [
        'password' => '',
        'password_confirm' => '',
    ];

    protected function rules(): array
    {
        return [
            'formData.password' => ['required', 'string', 'min:8'],
            'formData.password_confirm' => ['required', 'string', 'min:8', 'confirmed:formData.password'],
        ];
    }

    protected function messages(): array
    {
        return [
            'formData.password.required' => 'Kata sandi wajib diisi.',
            'formData.password.string' => 'Kata sandi harus berupa teks.',
            'formData.password.min' => 'Kata sandi minimal 8 karakter.',

            'formData.password_confirm.required' => 'Konfirmasi kata sandi wajib diisi.',
            'formData.password_confirm.string' => 'Konfirmasi kata sandi harus berupa teks.',
            'formData.password_confirm.min' => 'Konfirmasi kata sandi minimal 8 karakter.',
            'formData.password_confirm.confirmed' => 'Kata sandi tidak sama.',
        ];
    }

    public function savePassword()
    {
        $this->validate();

        \App\Models\User::find($this->formData['id'])->update($this->formData);

        Notification::make()
            ->title('Password berhasil diubah')
            ->success()
            ->send();
        Flux::modal('update-admin-password-modal')->close();
        $this->dispatch('refreshTable');
    }

     #[On('openUpdatePasswordModal')]
     public function openUpdatePasswordModal($record)
    {
        $this->formData = $record;
        Flux::modal('update-admin-password-modal')->show();
    }

}
?>
{{-- Update Admin Password Modal --}}
<flux:modal name="update-admin-password-modal" class="md:w-[520px]">
    <form wire:submit.prevent="savePassword">
        <div class="space-y-3">
            <div>
                <flux:heading size="lg">
                    Ubah Kata Sandi Admin
                </flux:heading>
            </div>
            <flux:input wire:model.defer="formData.password" type="password" label="Kata Sandi" placeholder="Kata Sandi" />
            <flux:input wire:model.defer="formData.password_confirm" type="password" label="Konfirmasi Kata Sandi" placeholder="Ketik ulang kata sandi" />

            <div class="flex mt-8">
                <flux:spacer />
                <flux:button type="submit" variant="filled" class="!bg-primary !text-white">Simpan</flux:button>
            </div>
        </div>
    </form>
</flux:modal>
