<?php

namespace App\Rules;

use App\Helpers\JadwalHelper;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class JatahJpRule implements ValidationRule
{
    protected $periodeId;

    public function __construct($periodeId)
    {
        $this->periodeId = $periodeId;
    }
    
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
         $result = JadwalHelper::validateJp($value, $this->periodeId);

        if (!$result['valid']) {
            $fail($result['message']);
            return;
        }
    }
}
