<?php

namespace App\Lib;

use App\Lib\Enums\ImagePath;
use \Illuminate\Http\UploadedFile;
use App\Lib\Lib;
use App\Lib\UtilitiesTypes\CustomUploadedFile;
use App\Lib\UtilitiesTypes\FileProccessStatus;
use App\Lib\UtilitiesTypes\ProccessStatus;


class FileHandler{
    public static function saveInPublic(UploadedFile|null $file , ImagePath $path = ImagePath::general):FileProccessStatus{
        try{
            if($file){
                $fileName = (Lib::generateRandomString(20)).'_'.time() . '_' . ($file->getClientOriginalName());

                $destinationPath = public_path('assets/uploads/images'.$path->value);
                $file->move($destinationPath, $fileName);

                $fullPath = public_path('assets/uploads/images'.$path->value.'/'.$fileName);

                if (file_exists($fullPath)) {
                    $newFile = new CustomUploadedFile(
                        $fileName,
                        $fullPath,
                    );

                    return new FileProccessStatus(true , '' , $newFile);
                } else {
                    return new FileProccessStatus(false, 'failed' , null);
                }
            }else{
                $newFile = new CustomUploadedFile(
                    'empty.png',
                    '',
                );
                return new FileProccessStatus(true , '' , $newFile);
            }
        }
        catch(\Illuminate\View\ViewException $e){
            return new FileProccessStatus(false , $e->getMessage(),null);
        }
        catch(\Exception $e){
            return new FileProccessStatus(false , $e->getMessage(),null);
        }
    }

    public static function deleteFromPublic(string $fileName  , ImagePath $path = ImagePath::general):ProccessStatus{
        if($fileName === 'empty.png') return new ProccessStatus(true , '');
        try{
            $fullPath = public_path('assets/uploads/images'.$path->value.'/'.$fileName);
            if (file_exists($fullPath)) {
                unlink($fullPath);
                return new ProccessStatus(true, '');
            } else {
                return new ProccessStatus(false, 'failed');
            }
        }catch(\Exception $e){
            return new ProccessStatus(false , $e->getMessage());
        }
    }

    public static function updateInPublic(string $oldFild , UploadedFile|null $file , ImagePath $path = ImagePath::general):FileProccessStatus{
        if($oldFild === 'empty.png')return FileHandler::saveInPublic($file,$path);

        $remove = FileHandler::deleteFromPublic($oldFild , $path);
        if($remove->status)return FileHandler::saveInPublic($file,$path);

        return new FileProccessStatus(false , '' , null);
    }

    public static function PublicFileExists($value){
        $path = ltrim(parse_url($value, PHP_URL_PATH), '/');
        $filePath = public_path($path);
        return file_exists($filePath);
    }
}



?>
