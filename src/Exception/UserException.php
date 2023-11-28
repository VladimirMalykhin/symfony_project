<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;



class UserException extends HttpException
{
    public function __construct(string $message = '', \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct(402, $message, $previous, $headers, $code);
    }
}