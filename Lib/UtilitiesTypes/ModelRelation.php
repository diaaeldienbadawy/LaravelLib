<?php
namespace App\Lib\UtilitiesTypes;

use App\Lib\Http\HttpStructure\AdvancedModel;
use App\Lib\Http\HttpStructure\AdvancedRepository;

class ModelRelation{
    public string $relation;
    public AdvancedRepository $rep;

    public function __construct(string $relation  , AdvancedRepository $rep){
        $this->relation = $relation;
        $this->rep = $rep;
    }
}
