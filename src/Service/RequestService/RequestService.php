<?php

namespace App\Service\RequestService;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class RequestService
{
    private $httpClient;

    public function __construct(
        HttpClientInterface $httpClient
    ) {
        $this->httpClient = $httpClient;
    }


    public function request(
        string $method
    ) {
        $response = $this->httpClient->request(
            $method,
            'https://api.letsenhance.io/v1/pipeline',
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-API-KEY' => 'a1a9f9a2890a7fe9d76f1fd4223dd1098deeab1d'
                ],
                'json' => [
                    'source' => [
                        'http' => [
                            'url' => 'https://letsenhance.io/docs/assets/samples/burger.jpg'
                        ]
                    ],
                    'operations' => [

                        'op' => 'preset/product',
                        'width' => 4800,
                        'height' => 1888,
                        'background_removal' => false,
                        'jpeg_qf' => 85

                    ],
                    'sink' => [
                        'temp_store' => true
                    ]

                ]
            ]
        );
        return $response;
    }

}

