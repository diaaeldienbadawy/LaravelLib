<?php
namespace App\Lib;
use Illuminate\Http\Client\Request;


class Lib
{
    private static function error($error)
    {
        if ($error === null) return null;
        $errors = [
            'unknown' => ['code' => '00001', 'messege' => 'خطأ غير متوقع'],
            'invalidData' => ['code' => 'A0001', 'messege' => 'البيانات غير صالحة'],
            'userNotFound' => ['code' => 'A0002', 'messege' => 'لا يوجد مستخدم بهذه البيانات'],
            'invalidAuthToken' => ['code' => 'A0003', 'messege' => 'انتهاء صلاحية الجلسة'],

            'expiredAccessToken' => ['code' => 'A0004', 'messege' => ''],
            'expiredToken' => ['code' => 'A0004', 'messege' => ''],
            'invalidDevice' => ['code' => 'A0006', 'messege' => 'المتصفح لا يملك صلاحية'],
            'permission' => ['code' => 'A0006', 'messege' => 'لا تملك الصلاحية لهذا الاجراء'],
            'noPermission' => ['code' => 'P0001', 'messege' => 'لا يوجد صلاحية لتنفيذ هذا الاجراء'],
            'maybeExist' => ['code' => 'Q0001', 'messege' => 'البيانات قد تكون مسجلة مسبقا'],
            'empty' => ['code' => 'D0001', 'messege' => 'لا توجد بيانات'],
            'failTrans' => ['code' => 'T0001', 'messege' => 'لم يتم تسجيل التحويل بنجاح'],

            'deleteRestrict' => ['code' => 'D0002', 'messege' => 'لا يمكن حذف العنصر'],

            'sizeExcceds' =>['code' => 'V0001' , 'messege' => 'حجم الملف اكبر من المسموح']
        ];
        if(array_key_exists($error,$errors))return $errors[$error];
        else return ['code'=>'00000' , 'messege'=>$error ];
    }
    public static function getCurrentLang()
    {
        return app()->getLocale();
    }

    public static function returnError($error)
    {
        return response()->json([
            'status' => false,
            'error_code' => Lib::error($error)['code'],
            'messege' => Lib::error($error)['messege']
        ]);
    }
    public static function returnGeneralError($error, $errorCode = '')
    {
        return response()->json([
            'status' => false,
            'error_code' => $errorCode,
            'messege' => $error
        ]);
    }

    public static function returnSuccessMessage($messege = "")
    {
        return [
            'status' => true,
            'error_code' => "S000",
            'messege' => $messege
        ];
    }

    public static function returnData($value, $messege = "", $error = null)
    {
        return response()->json([
            'status' => true,
            'error_code' => Lib::error($error)['code'] ?? "S000",
            'messege' => $messege,
            'data' => $value
        ]);
    }
    public static function returnDataArray($value, $messege = "", $error = null)
    {
        return [
            'status' => true,
            'error_code' => Lib::error($error)['code'] ?? "S000",
            'messege' => $messege,
            'data' => $value
        ];
    }


    //////////////////
    public static function returnValidationError($code = "E001", $validator)
    {
        return Lib::returnError($code, $validator->errors()->first());
    }

    public static function returnValidatorErrorField($validator)
    {
        $inputs = array_keys($validator->errors()->toArray());
        return $inputs[0];
    }

    public static function returnCodeAccordingToInput($validator)
    {
        $inputs = array_keys($validator->errors()->toArray());
        $code = Lib::getErrorCode($inputs[0]);
        return $code;
    }

    public static function getErrorCode($input)
    {
        if ($input == "name")
            return 'E0011';

        else if ($input == "password")
            return 'E002';

        else if ($input == "mobile")
            return 'E003';

        else if ($input == "id_number")
            return 'E004';

        else if ($input == "birth_date")
            return 'E005';

        else if ($input == "agreement")
            return 'E006';

        else if ($input == "email")
            return 'E007';

        else if ($input == "city_id")
            return 'E008';

        else if ($input == "insurance_company_id")
            return 'E009';

        else if ($input == "activation_code")
            return 'E010';

        else if ($input == "longitude")
            return 'E011';

        else if ($input == "latitude")
            return 'E012';

        else if ($input == "id")
            return 'E013';

        else if ($input == "promocode")
            return 'E014';

        else if ($input == "doctor_id")
            return 'E015';

        else if ($input == "payment_method" || $input == "payment_method_id")
            return 'E016';

        else if ($input == "day_date")
            return 'E017';

        else if ($input == "specification_id")
            return 'E018';

        else if ($input == "importance")
            return 'E019';

        else if ($input == "type")
            return 'E020';

        else if ($input == "message")
            return 'E021';

        else if ($input == "reservation_no")
            return 'E022';

        else if ($input == "reason")
            return 'E023';

        else if ($input == "branch_no")
            return 'E024';

        else if ($input == "name_en")
            return 'E025';

        else if ($input == "name_ar")
            return 'E026';

        else if ($input == "gender")
            return 'E027';

        else if ($input == "nickname_en")
            return 'E028';

        else if ($input == "nickname_ar")
            return 'E029';

        else if ($input == "rate")
            return 'E030';

        else if ($input == "price")
            return 'E031';

        else if ($input == "information_en")
            return 'E032';

        else if ($input == "information_ar")
            return 'E033';

        else if ($input == "street")
            return 'E034';

        else if ($input == "branch_id")
            return 'E035';

        else if ($input == "insurance_companies")
            return 'E036';

        else if ($input == "photo")
            return 'E037';

        else if ($input == "logo")
            return 'E038';

        else if ($input == "working_days")
            return 'E039';

        else if ($input == "insurance_companies")
            return 'E040';

        else if ($input == "reservation_period")
            return 'E041';

        else if ($input == "nationality_id")
            return 'E042';

        else if ($input == "commercial_no")
            return 'E043';

        else if ($input == "nickname_id")
            return 'E044';

        else if ($input == "reservation_id")
            return 'E045';

        else if ($input == "attachments")
            return 'E046';

        else if ($input == "summary")
            return 'E047';

        else if ($input == "user_id")
            return 'E048';

        else if ($input == "mobile_id")
            return 'E049';

        else if ($input == "paid")
            return 'E050';

        else if ($input == "use_insurance")
            return 'E051';

        else if ($input == "doctor_rate")
            return 'E052';

        else if ($input == "provider_rate")
            return 'E053';

        else if ($input == "message_id")
            return 'E054';

        else if ($input == "hide")
            return 'E055';

        else if ($input == "checkoutId")
            return 'E056';

        else
            return "";
    }


    //Tokens


    //mine
    public static function NoSqlInj($input)
    {

        // إزالة علامات HTML
        $sanitizedInput = strip_tags($input);

        // إزالة أو استبدال الأحرف الخاصة التي يمكن استخدامها في حقن SQL
        $sanitizedInput = preg_replace('/[\'\";`\--]/', '', $sanitizedInput);

        return $sanitizedInput;
    }

    public static function isValidEmail($email)
    {
        $email = self::NoSqlInj($email);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) return $email;
        return false;
    }

    public static function isValidUTCDateTimeString($dateTimeString)
    {
        //$pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}Z$/';
        $pattern = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/';
        // التحقق من تطابق النص مع الصيغة
        return preg_match($pattern, $dateTimeString);
    }

    public static function isValidYMDDateString($dateTimeString)
    {
        $pattern = '/^\d{4}-\d{2}-\d{2}$/';
        return preg_match($pattern, $dateTimeString);
    }
    public static function isValidImage(Request $request, $fieldName = 'img')
    {
        // التحقق من أن الحقل موجود وليس فارغًا
        if (!$request->hasFile($fieldName)) {
            return false;
        }

        $file = $request->file($fieldName);

        // التحقق من أن الملف ليس فارغًا وأنه ملف صورة
        if (!$file->isValid() || !in_array($file->extension(), ['jpeg', 'jpg', 'png', 'gif', 'svg'])) {
            return false;
        }

        return true;
    }


    public static function startsWithPlusAndDigits($value)
    {
        // التحقق من وجود الرمز "+" وأن بقية القيمة تحتوي على أرقام فقط
        return preg_match('/^\+\d+$/', $value) === 1;
    }

    public static function  getDateAfterDays($days)
    {
        if (!is_numeric($days)) $days = 0;
        // تاريخ اليوم
        $currentDate = time();

        // إضافة 30 يوماً (30 * 24 ساعة * 60 دقيقة * 60 ثانية)
        $futureDate = $currentDate + ($days * 24 * 60 * 60);

        // تنسيق التاريخ باستخدام gmdate
        $formattedDate = gmdate('Y-m-d', $futureDate);

        return $formattedDate;
    }

    public static function  getDateAfterHrs($Hrs)
    {
        if (!is_numeric($Hrs)) $Hrs = 0;
        // تاريخ اليوم
        $currentDate = time();

        // إضافة 30 يوماً (30 * 24 ساعة * 60 دقيقة * 60 ثانية)
        $futureDate = $currentDate + ($Hrs * 60 * 60);

        // تنسيق التاريخ باستخدام gmdate
        $formattedDate = gmdate('Y-m-d H:i:s', $futureDate);

        return $formattedDate;
    }

    public static function  getDateAfterMinutes($Mins)
    {
        if (!is_numeric($Mins)) $Mins = 0;
        // تاريخ اليوم
        $currentDate = time();

        // إضافة 30 يوماً (30 * 24 ساعة * 60 دقيقة * 60 ثانية)
        $futureDate = $currentDate + ($Mins * 60);

        // تنسيق التاريخ باستخدام gmdate
        $formattedDate = gmdate('Y-m-d H:i:s', $futureDate);

        return $formattedDate;
    }

    public static function isAlphanumeric($input)
    {
        return ctype_alnum($input);
    }

    public static function generateRandomString($length = 6)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
