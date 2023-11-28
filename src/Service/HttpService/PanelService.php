<?php

namespace App\Service\HttpService;

use App\SystemService\HttpRequestService\HttpRequestService;
use Symfony\Component\HttpClient\HttpClient;

class PanelService
{
	private $url = ""; 
	
	
	private function Auth()
	{
		$requestService = new HttpRequestService();
		$login = '';
		$password = '';
		$data = ['username' => $login, 'password' => $password]; 
		$httpResponse = $requestService->MakeRequest('POST', $this->url.'', [], $data, 'json');
        $token = $httpResponse->toArray()['token'];
		return $token;
	}
	
	
	public function GetCategories()
	{
		$requestService = new HttpRequestService();
		$token = $this->Auth();
		//$httpResponse = $requestService->MakeRequest('GET', $this->url.'/categories?limit=20000&offset=0', ['Authorization' => 'Bearer '.$token], [], 'body');
		$httpClient = HttpClient::create();
		$response = $requestService->MakeRequest('GET', $this->url."/categories?limit=20000&offset=0", ['Authorization' => 'Bearer '.$token], []);
		return json_decode($response->getContent(), true)['data'];
	}
}