<?php
namespace App\Service\CustomException;

use Error;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class CustomException extends Error implements Throwable
{
    protected $code;
    protected $message;

    public function __construct($code, $message)
    {
        $this->code = $code;
        $this->message = $message;
        parent::__construct();
    }


    public function create_response()
    {
        $json_response = new JsonResponse(['data' => ['code' => $this->code, 'message' => $this->message]]);
        $json_response->setStatusCode($this->code);
        return $json_response;
    }


    public function __toString()
    {
        $this->create_response();
    }
}
