<?php

namespace App\Controller;

use App\Service\RequestService\RequestService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ChangeImageController extends AbstractController
{

    /**
     * @Route("/api/image/change", name="change_image")
     */
    public function index(Request $request): Response
    {
        $request_object = json_decode($request->getContent(), true);
        $url = $request_object['image'];
        $epackage_number = $request_object['epackage'];
        $locale = $request_object['locale'];
        $template = $request_object['template'];
        $extensions = explode('.', $url);
		return new JsonResponse(['data' => ['code' => 200, 'message' => $url]]);
		/*
        if(strpos($url, 'temp')){
            $url_image = $this->getParameter('public_directory').$url;
            $url_current = $this->getParameter('domain') . $url;
        } else {
            $url = str_replace('../', '', $url);
            $url_image = $this->getParameter('epacks_directory'). '/' . $epackage_number . '/' . $locale . '/minisite/' . $template . '/' . $url;
            $url_current = $this->getParameter('domain') . '/uploads/' . $epackage_number . '/' . $locale . '/minisite/' . $template . '/' . $url;
        }
        if(!file_exists($url_image)){
            return new JsonResponse(['data' => ['code' => 404, 'message' => 'No such image' ]]);
        }
        $image_size = getimagesize($url_image);
        if(isset($request_object['width']) && $request_object['width'] > 0){
            $width = $request_object['width'];
        } else {
            $width = $image_size[0];
        }
        $scale = $width / $image_size[0];
        $height_image = $image_size[1] * $scale;
        $httpClient = HttpClient::create();
        
        $extensions[1] == 'jpg' ? $type_image = 'jpeg' : $type_image = $extensions[1];
        $response = $httpClient->request('POST', 'https://api.claid.ai/v1-beta1/image/edit',[
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer b3c92e00bd7e4a1d8d725812b81ac045'
            ],
            'json' => [
                'input' => $url_current,
                'operations' => [
                    'restorations' => [
                        'upscale' => $request_object['preset']
                    ],
                    'resizing' => [
                        'width' => (int)$width,
                        'height' => (int)$height_image,
                        'fit' => 'cover'
                    ]
                ],
                'output' => [
                    'format' => $type_image == 'jpeg' ? [
                        'type' => $type_image,
                        'quality' => 100,
                        'progressive' => true

                    ] : $type_image
                ]
            ]
            ]
        );
        $response_content = json_decode($response->getContent(), true);
        $fp = fopen('/home/my24ttl/logs', 'a');
        fwrite($fp, json_encode($response->getHeaders()).'\n');
        
       

        $new_image = $this->download_img($response_content['data']['output']['tmp_url'], $extensions[1]);

        return new JsonResponse(['data' => ['code' => 200, 'message' => $new_image]]);
		*/
    }


    private function download_img($site_link, $extension){
        $filename = time();
        $rand_name = rand(1000000, 9999999);
        $imagename = $filename . $rand_name . '.' . $extension;
        $content = file_get_contents($site_link);
        $fp = fopen($this->getParameter('temp_directory').'/img/'. $imagename, "w");
        fwrite($fp, $content);
        fclose($fp);
        return '/temp/img/' . $imagename;
    }



}
