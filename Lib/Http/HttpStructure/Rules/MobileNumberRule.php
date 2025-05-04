<?php
namespace App\Lib\Http\HttpStructure\Rules;

use Illuminate\Contracts\Validation\ValidationRule;

class MobileNumberRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if($value){
            $pattern = '/^\+(\d+)[\s-]?(\d+)$/';

            if (!preg_match($pattern, $value)) {
                $fail('invalidData');
            }
        }
    }
}
