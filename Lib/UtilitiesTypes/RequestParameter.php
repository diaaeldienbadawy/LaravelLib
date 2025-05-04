<?php
namespace App\Lib\UtilitiesTypes;

use App\Lib\Http\HttpStructure\Enums\ParameterOperator;

class RequestParameter{
    public ParameterOperator $operator;
    public string $key;
    public string $value;
    public bool $nullable = false;
    
    public function __construct(string $key, ParameterOperator $operator , string $value , bool $nullable = false )
    {
        $this->key = $key;
        $this->operator = $operator;
        $this->value = $value;
        $this->nullable = $nullable;
    }
}
