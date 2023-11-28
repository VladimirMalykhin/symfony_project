<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PreviewEpackageController extends AbstractController
{/**
     * @Route("/api/epackage/preview", name="preview_epackage", methods={"GET"})
     */
    public function index(Request $request)
    {
        $url = $request->get('url');
        $epackageContent = file_get_contents($url);
        if(!$epackageContent){
        	return new JsonResponse(['data' => ['code' => 402, 'message' => 'Wrong url']]);
        } 
        $epackageArray = (array)json_decode($epackageContent);
        $epackageArray['content'] = str_replace('&lt;', '<', $epackageArray['content']);
        $epackageArray['content'] = str_replace('&gtl', '>', $epackageArray['content']);
        $epackageArray['content'] = str_replace("\n", '', $epackageArray['content']);
        $epackageArray['content'] = str_replace("\t", '', $epackageArray['content']);
        if(strpos($url, 'index.html')){
            $urlTemplate = str_replace('content/html/index.html', '', $url);
        }
        if(strpos($url, 'index.json')){
            $urlTemplate = str_replace('content/json/index.json', '', $url);
        }
        $epackageArray['content'] = str_replace('../../', $urlTemplate, $epackageArray['content']);
        if(!$epackageArray['content']){return new JsonResponse(['data' => ['code' => 402, 'message' => 'Wrong url']]);}
		return new Response($epackageArray['content']);
        
    }
}