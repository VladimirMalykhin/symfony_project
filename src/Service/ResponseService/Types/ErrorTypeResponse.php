<?php

namespace App\Service\ResponseService\Types;

class ErrorTypeResponse {

    private $code;
    private $message;
    private $parameters;

    public function __construct(array $data, array $parameters)
    {
        $this->code = $data['code'];
        $this->message = $data['message'];
        $this->parameters = $parameters;
    }


    public function toArray() :array
    {
        return [
            'code' => $this->code,
            'message' => $this->message
        ];
    }

}