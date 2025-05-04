<?php
namespace App\Lib\Http\HttpStructure\Rules;

use App\Lib\Enums\ImagePath;
use App\Lib\FileHandler;
use App\Lib\FileService;
use Illuminate\Contracts\Validation\ValidationRule;

class FileOrNameRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if($value){
            if (!is_string($value) && !($value instanceof \Illuminate\Http\UploadedFile)) {
                $fail('invalidData');
            }

            if ($value instanceof \Illuminate\Http\UploadedFile) {
                if (!$value->isValid()) {
                    $fail('invalidData');
                }
                if (!in_array($value->extension(), ['jpeg', 'png', 'jpg'])) {
                    $fail('صيغة الملف غير صحيحة');
                }
                if ($value->getSize() > 2048 * 1024) {
                    $fail('حجم الملف اكبر من المسموح');
                }
            }
        }
    }
}
