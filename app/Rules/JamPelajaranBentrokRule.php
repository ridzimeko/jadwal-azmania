<?php

namespace App\Rules;

use App\Models\JamPelajaran;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class JamPelajaranBentrokRule implements ValidationRule
{
    protected $jamId;        // untuk mode edit

    public function __construct($jamId = null)
    {
        $this->jamId = $jamId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $mulai = request()->input('jam_mulai');
        $selesai = request()->input('jam_selesai');

        dd($mulai, $selesai, $this->jamId);
        
        // Jika belum lengkap, biarkan rule tidak menolak dulu
        if (!$mulai || !$selesai) {
            return;
        }


        // Cek apakah bentrok
        $exists = JamPelajaran::when($this->jamId, fn($q) => $q->where('id', '!=', $this->jamId))
            ->where(function ($q) use ($mulai, $selesai) {
                $q->where('jam_mulai', '<', $selesai)
                    ->where('jam_selesai', '>', $mulai);
            })
            ->exists();

        if ($exists) {
            $fail("Jam pelajaran bentrok dengan data yang sudah ada.");
        }
    }
}
