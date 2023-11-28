<?php

namespace App\SystemService\HttpRequestService;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\HttpClient;


class HttpRequestService
{
	private $httpClient;

	
	public function MakeRequest(
		string $method,
		string $url,
		array $headers,
		array $data,
		string $dataType = ''
	){
		$httpClient = HttpClient::create();
		$url = str_replace('&amp;', '&', $url);
		$requestData = [];
		if($data != []) $requestData[$dataType] = $data;
		if($headers != []) $requestData['headers'] = $headers;
		$response = $httpClient->request(
            $method,
            $url,
            $requestData
        );
        return $response;
	}
	
}