<?php

namespace App\Infrastructure\ParametersInterface;


class ParametersInterface
{
    private array $params = [
        'media' => ['src', 'srcMob', 'videoSrc', 'poster', 'srcTablet', 'srcDesktop', 'mainImage', 'blockImage', 'sidebarImage'],
        'text' => ['titleContent', 'descriptionContent', 'content', 'disclaimerContent', 'mainBodyText', 'mainHeadlineText', 'mainSubHeadlineText', 'mainImageAltText', 'blockBodyText'],
	];


    private array $array_params = [
        'media_array' => ['rowImages'],
        'text_array' => ['rowContent']
    ];


	private function getParams($name) :array
    {
        return $this->params[$name];
    }


    private function getArrayParams($name) :array
    {
        return $this->array_params[$name];
    }


	public function getUsedParams($name, $item) :array
    {
        $results = [];
        foreach($this->getParams($name) as $param){
            if(isset($item[$param])){
                $results[] = $param;
            }
        }
        return $results;
    }


    public function getUsedArrayParams($name, $item) :array
    {
        $results = [];
        foreach($this->getArrayParams($name) as $param){
            if(isset($item[$param])){
                $results[] = $param;
            }
        }
        return $results;
    }
}