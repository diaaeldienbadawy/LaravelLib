<?php
namespace App\Lib;
use Illuminate\Http\Request;
use App\Models\FQA;

class Getter{
    public static function getFQAs($page, $pageCount = 10){
        $result = FQA::paginate($pageCount,['*'], 'page' ,$page );
        return $result;
    }
    public static function getQuestions($count = 5){
        return FQA::take($count)->get();;
    }
}
