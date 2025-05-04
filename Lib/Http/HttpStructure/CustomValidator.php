<?php
namespace App\Lib\Http\HttpStructure;


use App\Lib\FileService;
use App\Lib\Http\HttpStructure\Exceptions\CustomValidationException;
use App\Lib\Http\HttpStructure\Rules\StringOrNumber;
use App\Lib\Lib;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomValidator{
    public array $rules =[];
    public array $messeges=[];
    public array $fileFields = [];

    public function validate(Request $request ):array{
        $data = $request->all();

        if(array_key_exists('list',$data)){
            if(is_array($data['list'])){
                $validatedItems = [];
                foreach ($data['list'] as $item) {
                    $validated = $this->validating($item);
                    array_push($validatedItems , $validated);
                }
                return ['list'=>$validatedItems];
            }else {
                return $this->validating($request->all());
            }
        }else {
            return $this->validating($request->all());
        }

    }

    private function validating(array $data){
        $validator =  Validator::make(
            $data,
            $this->rules,
            $this->messeges
        );

        if($validator->fails()){
            throw new CustomValidationException(Lib::returnError(count($validator->errors())>0?$validator->errors()->first():"unknown"));
        }
        return $this->refillFiles($validator->validated());
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
