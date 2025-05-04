<?php
namespace App\Lib;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class RequestValidation{

    public static $errors = [
        'The password field must be at least 5 characters.' =>'يجب ان لا تقل كلمة السر عن 5 خانات'
    ];

    public static function returnError($text){
        if(array_key_exists($text,RequestValidation::$errors)){
            return Lib::returnGeneralError(RequestValidation::$errors[$text]);
        }
        else return Lib::returnError('unknown');
    }

    public static function requestTry(callable $callback){
        try{
           return $callback();
        }
        catch(ModelNotFoundException $e){ return Lib::returnError('unknown'); }
        catch(QueryException $e){ return Lib::returnError('unknown'); }
        catch(Exception $e){ return RequestValidation::returnError($e->getMessage()); }
    }

    public static function pageReq(Request $request){
        $page = $request->page;
        if($page == null) return '1';
        if($page == '')return '1';
        if(!is_numeric($page))return '1';
        return $page;
    }
    public static function idReq(Request $request){
        $id = $request->id;
        if($id == null) return '1';
        if($id == '')return '1';
        if(!is_numeric($id))return '1';
        return $id;
    }

    public static function IsExists($text){
        $text = strip_tags($text);
        if (is_null($text)) {
            return false;
        }
        if (empty($text)) {
            return false;
        }
        return $text;
    }
    public static function RegexVal($text , $regex){
        $text = RequestValidation::IsExists($text);
        if (!$text) return false;
        return preg_match( $regex, $text);
    }
    public static function IsAlphaNumeric($text){
        return RequestValidation::RegexVal($text ,'/^[a-zA-Z0-9]+$/');
    }
    public static function IsInteger($text){
        return RequestValidation::RegexVal($text ,'/^[0-9]+$/');
    }
    public static function IsNumeric($text){
        $text = RequestValidation::RegexVal($text ,'/^\d+(\.\d+)?$/');
        if($text) return round($text, 2);
        else return false;
    }
    public static function IsAlpha($text){
        return RequestValidation::RegexVal($text ,'/^[a-zA-Z]+$/');
    }
    public static function IsEmail($text){
        return RequestValidation::RegexVal($text ,'/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/');
    }
    public static function IsPhoneNumber($text){
        return RequestValidation::RegexVal($text ,'/^\+[0-9]+$/');
    }
    public static function IsDate($text){
        return RequestValidation::RegexVal($text ,'/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}(:\d{2})?)?$/');
    }
    public static function IsToken($text , $length = 255){
        if(!RequestValidation::IsAlphaNumeric($text))return false;
        if(strlen($text) != $length) return false;
        return true;
    }
}
