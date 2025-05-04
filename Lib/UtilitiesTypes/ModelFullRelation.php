<?php

namespace App\Lib\UtilitiesTypes;

use App\Lib\Http\HttpStructure\AdvancedRepository;

class ModelFullRelation extends ModelRelation{
    public string $parent_key;


    public function __construct(string $relation  , AdvancedRepository $rep  , string $parent_key){
        parent::__construct($relation , $rep);
        $this->parent_key = $parent_key;
    }
}




?>
