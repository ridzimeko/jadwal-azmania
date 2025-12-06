<?php

namespace App\Rules;

use App\Helpers\JadwalHelper;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class JatahJpRule implements ValidationRule
{
    protected $periodeId;
    protected $jadwalId;

    public function __construct($periodeId, $jadwalId = null)
    {
        $this->periodeId = $periodeId;
        $this->jadwalId = $jadwalId;
    }
    
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
         $result = JadwalHelper::validateJp($value, $this->periodeId, $this->jadwalId);

        if (!$result['valid']) {
            $fail($result['message']);
            return;
        }
    }
}
