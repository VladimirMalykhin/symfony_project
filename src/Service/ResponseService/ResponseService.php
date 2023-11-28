<?php

namespace App\Service\ResponseService;

class ResponseService{
    private $responseObject;


    public function __construct($responseType, $epackage, array $parameters)
    {
        $this->responseObject = new $responseType($epackage, $parameters);
    }


    public function toArray() :array
    {
        return ['data' => $this->responseObject->toArray()];
    }


}