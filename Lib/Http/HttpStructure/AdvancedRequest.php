<?php

namespace App\Lib\Http\HttpStructure;


use App\Lib\Lib;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Lib\FileService;

class AdvancedRequest extends FormRequest
{
    public array $fileFields = [];

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Lib::returnError($validator->errors()->first()));
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();


        return $this->refillFiles($validated);
    }

    private function refillFiles(array $validated):array{
        foreach($validated as $field => &$value ){
            if(is_array($value)){
                $value =  $this->refillFiles($value);
            } else {
                if(array_key_exists($field , $this->fileFields)){
                    $fileService = new FileService($value , $this->fileFields[$field]);
                    if($fileService->saveInPublic()->status) $value = $fileService->newfile->name;
                }
            }
        }
        return $validated;
    }
}
