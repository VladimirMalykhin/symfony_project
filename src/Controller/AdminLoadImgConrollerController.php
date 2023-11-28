<?php

namespace App\Controller;

use App\Service\Form\ImageType;
use App\Service\FormService\FormService;
use mysql_xdevapi\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use App\Security\CustomAuthenticator;
use App\Entity\Epacks;
use App\Service\MediaImageService\MediaImageService;



class AdminLoadImgConrollerController extends AbstractController
{

    private FormService $formService;


    /**
     * @Route(path="/api/load/img", name="admin_load_img_conroller", methods={"POST"})
     */
    public function index(Request $request): Response
    {
        $filename = time();
        $rand_name = rand(1000000, 9999999);

        $responses = (array)json_decode($request->getContent(), true);
        if($request->request->get('height') && $request->request->get('width')){
            $height_image = $request->request->get('height');
            $width_image = $request->request->get('width');
        }
        $file = $request->files->get('file');

        if ($file != null){
            $extension = $file->guessExtension();
        } else {
            $json_response = new JsonResponse(['data' =>['code' => 402, 'message' => 'Size of file is more than 10 Mb']]);
            $json_response->setStatusCode(402);
            return $json_response;
        }

        $response = [];
        /*
        $formData = $this->formService->handleForm(ImageType::class);
        return new JsonResponse(['data' => ['code' =>200, 'data' => $formData]]);
        */
        if($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png' || $extension == 'svg' || $extension == 'webp' || $extension == 'pdf'){
            if($file->getSize() < 10000000){
				$sizeImage = $file->getSize();
                $image_name = $filename.$rand_name.'.'.$extension;
                if($extension == 'pdf'){
                    $file->move($this->getParameter('temp_directory').'/pdf', $filename.$rand_name.'.'.$extension);
                } else {
                    if(isset($height_image) && isset($width_image)){
                        $image_sizes = getimagesize($file);
                        if($image_sizes[0] != $width_image || $image_sizes[1] != $height_image){
                            $json_response = new JsonResponse(['data' =>['code' => 402, 'message' => 'Sizes of image should be '.$width_image.' x '.$height_image ]]);
                            $json_response->setStatusCode(402);
                            return $json_response;
                        }
                    }

                    $file->move($this->getParameter('temp_directory').'/img', $filename.$rand_name.'.'.$extension);
                }
                

                if($extension != 'pdf' && $sizeImage > 300000){
                    $optimizerChain = OptimizerChainFactory::create();

                    $optimizerChain->optimize($this->getParameter('temp_directory').'/img/'.$filename.$rand_name.'.'.$extension);
                }

            }
            else{
                $json_response = new JsonResponse(['data' =>['code' => 402, 'message' => 'Size of file is more than 10 Mb']]);
                $json_response->setStatusCode(402);
                return $json_response;
            }
        }

        else{
            $json_response = new JsonResponse(['data' =>['code' => 402, 'message' => 'Wrong type of image']]);
            $json_response->setStatusCode(402);
            return $json_response;
        }

        if($extension == 'pdf'){
            $folder = 'pdf';
        } else {
            $folder = 'img';
			$imageService = new MediaImageService($this->getParameter('temp_directory').'/'.$folder.'/'.$filename.$rand_name.'.'.$extension);
			$imageService->isResizing();
        }
        return new JsonResponse(['data' => ['code' =>200, 'data' => '/temp/'.$folder.'/'.$filename.$rand_name.'.'.$extension]]);
        
        
    }


    function compressImage($source, $destination, $quality, $extension) {

        if ($extension == 'jpeg')
            $image = imagecreatefromjpeg($source);

        elseif ($extension == 'gif')
            $image = imagecreatefromgif($source);

        elseif ($extension == 'png')
            $image = imagecreatefrompng($source);

        elseif ($extension == 'jpg')
            $image = imagecreatefromjpeg($source);
        $image_size = getimagesize($source);
        $dest_img = imagecreatetruecolor($image_size[0], $image_size[1]);
        $bg = imagecolorallocate($dest_img, 255, 255, 255);
        //imagefill($dest_img, 0, 0, $bg);
        if ($extension === 'png') {
            imagecolortransparent($dest_img, $bg);
        }
        if(isset($image) ){
            imagejpeg($image, $destination, $quality);
        } /* elseif(isset($image) && $extension == 'png'){
            imagepng($image, $destination);
        }*/
    }
}