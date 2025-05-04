<?php
namespace App\Lib\UtilitiesTypes;


class FileProccessStatus extends ProccessStatus{
    public ?CustomUploadedFile $file;
    public function __construct(bool $status , string $messege , ?CustomUploadedFile $file)
    {
        parent::__construct($status,$messege);
        $this->file = $file;
    }
}
