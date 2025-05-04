<?php
namespace App\Lib\Http\HttpStructure\Rules;

use App\Lib\Enums\ImagePath;
use App\Lib\FileHandler;
use App\Lib\FileService;
use Illuminate\Contracts\Validation\ValidationRule;

class FileOrNameOrUrlRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if($value){
            if (!is_string($value) && !($value instanceof \Illuminate\Http\UploadedFile)) {
                $fail('invalidData');
            }
            
            if(is_string($value)){
                $content = $value;
        
                if (preg_match('/src="([^"]+)"/', $content, $matches)) {
                    if(count($matches)>1){
                        $content = $matches[1]; // استخراج الرابط فقط
                        if (!filter_var($content, FILTER_VALIDATE_URL)) {
                            $fail($content);
                        }
                        $allowedDomains = ['youtube.com', 'www.youtube.com', 'vimeo.com', 'www.vimeo.com'];
                        $host = parse_url($content, PHP_URL_HOST);
                        
                        if (!in_array($host, $allowedDomains)) {
                            $fail('الرابط غير مسموح1');
                        }
                    }
                }
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
