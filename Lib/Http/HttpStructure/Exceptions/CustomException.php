<?php
namespace App\Lib\Http\Exceptions;

use App\Lib\Lib;
use Exception;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CustomException extends Exception
{
    public function __construct(string $messege, string $errorCode)
    {

        $this->message = $messege;
    }

    /**
     * Get the underlying response instance.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getResponse()
    {
        return Lib::returnError($this->message);
    }
}

