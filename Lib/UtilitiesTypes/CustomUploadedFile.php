<?php
namespace App\Lib\UtilitiesTypes;

use App\Lib\Http\HttpStructure\Enums\ImagePath;

class CustomUploadedFile{
    public string $fullPath;
    public string $name;
    public string $destinationPath;
    public string $trashPath;

    public function __construct(string $name ,ImagePath|null $destinationPath = null ){
        if($destinationPath){
            $this->destinationPath = ($destinationPath->value);
            $this->name =  basename($name);
            $this->fullPath = public_path($this->destinationPath.'/'.$name);
        }else{
            $this->name = basename($name);
            $this->destinationPath = dirname($name);
            $this->fullPath = $name;
        }
        $this->trashPath = base_path('trash/'.$this->name);
    }
}
