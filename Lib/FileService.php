<?php

namespace App\Lib;

use App\Lib\Http\HttpStructure\Enums\ImagePath;
use \Illuminate\Http\UploadedFile;
use App\Lib\Lib;
use App\Lib\UtilitiesTypes\CustomUploadedFile;
use App\Lib\UtilitiesTypes\FileProccessStatus;
use App\Lib\UtilitiesTypes\ProccessStatus;

class FileService{

    private UploadedFile|string|null $file;
    public CustomUploadedFile|null $newfile;
    private ImagePath $path;

    public function __construct(UploadedFile|string|null $file , ImagePath $path = ImagePath::general)
    {
        $this->file = $file;
        $this->path = $path;
        $this->convert();
    }

    private function convert(){
        if($this->file){
            if($this->file instanceof UploadedFile){
                $fileName = (Lib::generateRandomString(20)).'_'.time() . '_' . ($this->file->getClientOriginalName());
                $this->newfile = new CustomUploadedFile($fileName,$this->path);
            }else if(is_string($this->file)){
                $this->newfile = new CustomUploadedFile(basename($this->file),$this->path);
            }else $this->newfile = null;
        }else $this->newfile = null;
    }


    public function saveInPublic():ProccessStatus{
        try{
            if($this->newfile){
                if($this->file instanceof UploadedFile)
                    $this->file->move($this->newfile->destinationPath, $this->newfile->name);

                if (file_exists($this->newfile->fullPath)) {
                    return new ProccessStatus(true , '' );
                }else if(file_exists($this->newfile->trashPath)){
                    return $this->restore();
                }else return new ProccessStatus(false, 'failed' );
            }else{
                return new ProccessStatus(false , '');
            }
        }
        catch(\Illuminate\View\ViewException $e){
            return new ProccessStatus(false , $e->getMessage());
        }
        catch(\Exception $e){
            return new ProccessStatus(false , $e->getMessage());
        }
    }

    public function deleteFromPublic():ProccessStatus{
        try{
            if($this->newfile){
                if(file_exists($this->newfile->fullPath)){
                    if($this->moveToTrash()) return new ProccessStatus(true, '');
                    else return new ProccessStatus(true, 'failed');
                }else return new ProccessStatus(true, 'failed');
            }else return new ProccessStatus(true , '');
        }catch(\Exception $e){
            return new ProccessStatus(false , $e->getMessage());
        }
    }

    /*public function moveFile(CustomUploadedFile $file , string $newPath):CustomUploadedFile|null{
        if (file_exists($file->fullPath)) {
            if (rename($file->fullPath, $newPath.'/'.$file->name)) {
                return new CustomUploadedFile($file->name , $newPath);
            } else {
                return null;
            }
        } else {
            return null;
        }
    }*/
    public function moveToTrash():ProccessStatus{
        if (file_exists($this->newfile->fullPath)) {
            if (rename($this->newfile->fullPath, $this->newfile->trashPath)) {
                return new ProccessStatus(true , '');
            } else {
                return new ProccessStatus(true, 'failed');
            }
        } else {
            return new ProccessStatus(true, 'failed');
        }
    }
    public function restore():ProccessStatus{
        if($this->newfile){
            if (file_exists($this->newfile->trashPath)) {
                if (rename($this->newfile->trashPath, $this->newfile->fullPath)) {
                    return new ProccessStatus(true , '');
                } else {
                    return new ProccessStatus(false, 'failed');
                }
            } else {
                return new ProccessStatus(false, 'failed');
            }
        }else  return new ProccessStatus(false, 'failed');
    }
    /*public static function PublicFileExists($value){
        $path = ltrim(parse_url($value, PHP_URL_PATH), '/');
        $filePath = public_path($path);
        return file_exists($filePath);
    }*/
}



?>
