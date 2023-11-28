<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class AdminLoadVideoController extends AbstractController
{
    /**
     * @Route(path="/api/load/video", name="admin_load_video", methods={"POST"})
     */
    public function index(Request $request): Response
    {
        $filename = time();
        $rand_name = rand(1000000, 9999999);

        $file = $request->files->get('file');
        if ($file != null){
            $extension = $file->guessExtension();
        } else {
            $json_response = new JsonResponse(['data' =>['code' => 402, 'message' => 'Please upload a file less than 10 MB']]);
            $json_response->setStatusCode(402);
            return $json_response;
        }
        if($extension == 'mp4' || $extension == 'mov' || $extension == 'gif'){
            if($file->getSize() < 15728640){
                $video_name = $filename.$rand_name.'.'.$extension;

                $file->move($this->getParameter('temp_directory').'/video', $filename.$rand_name.'.'.$extension);
                if($extension == 'gif'){
                    $command = 'ffmpeg -f gif -i '. $this->getParameter('temp_directory').'/video/'. $filename.$rand_name.'.'.$extension .' -movflags faststart -pix_fmt yuv420p -vf "scale=trunc(iw/2)*2:trunc(ih/2)*2"  '.$this->getParameter('temp_directory')."/video/". $filename.$rand_name.".mp4";
                    $command = str_replace("\/", "/", $command);
                    exec($command);
                    unlink($this->getParameter('temp_directory').'/video/'. $filename.$rand_name.'.'.$extension);
                    return new JsonResponse(['data' => ['code' =>200, 'data' => '/temp/video/'.$filename.$rand_name.'.mp4']]);
                }
            }
            else{
                $json_response = new JsonResponse(['data' =>['code' => 402, 'message' => 'Please upload a file less than 15 MB']]);
                $json_response->setStatusCode(402);
                return $json_response;
            }
        }
        else{
            $json_response = new JsonResponse(['data' =>['code' => 402, 'message' => 'Wrong type of video']]);
            $json_response->setStatusCode(402);
            return $json_response;
        }

        return new JsonResponse(['data' => ['code' =>200, 'data' => '/temp/video/'.$filename.$rand_name.'.'.$extension]]);

    }
}
