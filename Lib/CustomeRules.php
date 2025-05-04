<?php
namespace App\Lib;

use App\Lib\Enums\ImagePath;

use function PHPUnit\Framework\fileExists;

class CustomeRules{
    public static function FileOrStringRule($attribute, $value, $fail) {
        if (!is_string($value) && !($value instanceof \Illuminate\Http\UploadedFile)) {
            $fail('The ' . $attribute . ' must be either a file or a string.');
        }

        if ($value instanceof \Illuminate\Http\UploadedFile) {
            if (!$value->isValid()) {
                $fail('The ' . $attribute . ' file upload is invalid.');
            }
            if (!in_array($value->extension(), ['jpeg', 'png', 'jpg'])) {
                $fail('The ' . $attribute . ' must be an image of type jpeg, png, or jpg.');
            }
            if ($value->getSize() > 2048 * 1024) {
                $fail('The ' . $attribute . ' must not exceed 2MB.');
            }
        }
    }
    public static function FileOrStringRules($attribute, $value, $fail , ImagePath $path) {
        if($value){
            if (!is_string($value) && !($value instanceof \Illuminate\Http\UploadedFile)) {
                $fail('invalidData');
            }

            if ($value instanceof \Illuminate\Http\UploadedFile) {
                if (!$value->isValid()) {
                    $fail('invalidData');
                }
                if (!in_array($value->extension(), ['jpeg', 'png', 'jpg'])) {
                    $fail('invalidData');
                }
                if ($value->getSize() > 2048 * 1024) {
                    $fail('حجم الملف اكبر من المسموح');
                }
            }

            $fileService = new FileService($value , $path);
            $file = $fileService->saveInPublic();
            if($file->status)
            return $fileService->newfile->name;
            else return null;
        }else return null;
    }
    public static function FileOrFileNameRule($attribute, $value, $fail) {
        if (!is_string($value) && !($value instanceof \Illuminate\Http\UploadedFile)) {
            $fail('The ' . $attribute . ' must be either a file or a string.');
        }

        if ($value instanceof \Illuminate\Http\UploadedFile) {
            if (!$value->isValid()) {
                $fail('The ' . $attribute . ' file upload is invalid.');
            }
            if (!in_array($value->extension(), ['jpeg', 'png', 'jpg'])) {
                $fail('The ' . $attribute . ' must be an image of type jpeg, png, or jpg.');
            }
            if ($value->getSize() > 2048 * 1024) {
                $fail('The ' . $attribute . ' must not exceed 2MB.');
            }
        }
    }
    public static function StringOrNumber($attribute, $value, $fail) {
        if (!is_string($value) && !($value instanceof \Illuminate\Http\UploadedFile)) {
            $fail('The ' . $attribute . ' must be either a file or a string.');
        }

        if ($value instanceof \Illuminate\Http\UploadedFile) {
            if (!$value->isValid()) {
                $fail('The ' . $attribute . ' file upload is invalid.');
            }
            if (!in_array($value->extension(), ['jpeg', 'png', 'jpg'])) {
                $fail('The ' . $attribute . ' must be an image of type jpeg, png, or jpg.');
            }
            if ($value->getSize() > 2048 * 1024) {
                $fail('The ' . $attribute . ' must not exceed 2MB.');
            }
        }else {
            if(!FileHandler::PublicFileExists($value))$fail('The ' . $attribute . ' file upload is not found.');
        }
    }
}
