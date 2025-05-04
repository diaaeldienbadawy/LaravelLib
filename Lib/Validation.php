<?php
namespace App\Lib;

class Validation {
    public static function NoSqlInj($input){

        // إزالة علامات HTML
        $sanitizedInput = strip_tags($input);

        // إزالة أو استبدال الأحرف الخاصة التي يمكن استخدامها في حقن SQL
        $sanitizedInput = preg_replace('/[\'\";`\--]/', '', $sanitizedInput);

        return $sanitizedInput;
    }

    public static function isValidEmail($email)
    {
        $email = self::NoSqlInj($email);
        if(filter_var($email, FILTER_VALIDATE_EMAIL)) return $email;
        return false;
    }

   public static function isValidUTCDateTimeString($dateTimeString)
    {
           //$pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}Z$/';
        $pattern = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/';
        // التحقق من تطابق النص مع الصيغة
        return preg_match($pattern, $dateTimeString);
    }

    public static function isValidYMDDateTimeString($dateTimeString)
    {
        $pattern = '/^\d{4}-\d{2}-\d{2}$/';
        return preg_match($pattern, $dateTimeString);
    }


    public static function startsWithPlusAndDigits($value)
    {
        // التحقق من وجود الرمز "+" وأن بقية القيمة تحتوي على أرقام فقط
        return preg_match('/^\+\d+$/', $value) === 1;
    }

}
