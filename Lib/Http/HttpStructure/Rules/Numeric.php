<?php
namespace App\Lib\Http\HttpStructure\Rules;

use Illuminate\Contracts\Validation\ValidationRule;

class Numeric implements ValidationRule
{
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if($value){
            if (!is_numeric($value)) {
                $fail('invalidData');
            }
        }
    }
}
