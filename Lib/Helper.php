<?php
namespace App\Lib;

class Helper{
    public static function  getDateAfterDays($days) {
        if(!is_numeric($days))$days = 0;
        // تاريخ اليوم
        $currentDate = time();

        // إضافة 30 يوماً (30 * 24 ساعة * 60 دقيقة * 60 ثانية)
        $futureDate = $currentDate + ($days * 24 * 60 * 60);

        // تنسيق التاريخ باستخدام gmdate
        $formattedDate = gmdate('Y-m-d', $futureDate);

        return $formattedDate;
    }

    public static function  getDateAfterHrs($Hrs) {
        if(!is_numeric($Hrs))$Hrs = 0;
        // تاريخ اليوم
        $currentDate = time();

        // إضافة 30 يوماً (30 * 24 ساعة * 60 دقيقة * 60 ثانية)
        $futureDate = $currentDate + ( $Hrs * 60 * 60);

        // تنسيق التاريخ باستخدام gmdate
        $formattedDate = gmdate('Y-m-d H:i:s', $futureDate);

        return $formattedDate;
    }

}





?>
