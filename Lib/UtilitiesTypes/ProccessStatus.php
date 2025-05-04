<?php
namespace App\Lib\UtilitiesTypes;

class ProccessStatus{
    public bool $status;
    public string $messege;

    public function __construct(bool $status , string $messege){
        $this->status = $status ;
        $this->messege = $messege;
    }
}
