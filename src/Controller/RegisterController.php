<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\User;

class RegisterController extends AbstractController
{
    /**
     * @Route("/api/load/video", name="load_video")
     */
    public function index(Request $request): Response
    {
        $filename = time();
        $rand_name = rand(1000000, 9999999);

        $file = $request->files->get('file');
        if ($file != null){
            $extension = 'mp4';
        } else {
            return new JsonResponse(['data' => ['code' => 402, 'message' => 'Please upload another file']]);
        }

        if($extension == 'mp4' || $extension == 'mov'){
            if($file->getSize() < 15728640){
                $video_name = $filename.$rand_name.'.'.$extension;

                $file->move($this->getParameter('temp_directory').'/video', $filename.$rand_name.'.'.$extension);
            }
            else{
                return new JsonResponse(['data' => ['code' => 402, 'message' => 'Size of file is more than 15 Mb']]);
            }
        }

        else{
            return new JsonResponse(['data' => ['code' => 402, 'message' => 'Wrong type of image']]);
        }
        return new JsonResponse(['data' => ['code' =>200, 'data' => '/temp/video/'.$filename.$rand_name.'.'.$extension]]);

    }
}
