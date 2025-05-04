<?php
namespace App\Lib\UtilitiesTypes;

use App\Lib\Http\HttpStructure\Enums\ParameterOperator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Parameters{

    protected array $params = [];

    public function __construct(array $params)
    {
        foreach($params as $param){
            if($param instanceof RequestParameter){
                array_push($this->params,$param);
            }
        }
    }

    public function injectParameters(Request $request , Builder $query):Builder{
        foreach($this->params as $param){
            $key = $param->key;
            if(isset($request->$key) || $param->nullable){
                $query = $query->where($param->key ,$param->operator->value, $param->value );
            }
        }
        return $query;
    }

    public function ConcParameters(Model $query):Model{
        foreach($this->params as $param){
            $query = $query->where($param->key,$param->operator->value , $param->value);
        }
        return $query;
    }
}
