<?php

namespace App\Controller;

use App\Repository\FontsRepository;
use App\Service\EpackageCleaner\EpackageCleaner;
use App\Service\LocaleParser\LocaleParser;
use App\Service\TemplateService\OzonService\OzonService;
use App\Service\TemplateParser\TemplateParser;
use App\Service\Typograf\Typograf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Service\Security\WebServiceUser;
//use Symfony\Component\Security\Core\Security;
use App\Entity\Epacks;
use App\Entity\Fonts;
use App\Entity\FormatFonts;

class AdminTemplateController extends AbstractController
{
    /**
     * @Route(path="/api/epackage_template/{number}", name="admin_template_epack",
     *     requirements={
     *          "number" = "%app.id_integer_regex%",
     *      },
     *     methods={"GET"})
     */
     public function index(string $number, Request $request): Response
     {

             $epacks = $this->getDoctrine()
                 ->getRepository(Epacks::class)
                 ->findOneBy([
                     'file' => $number
                 ]);

         if(!$epacks){
             $json_response = new JsonResponse(['data' =>['code' => 404, 'message' => 'No such E-pack']]);
             $json_response->setStatusCode(404);
             return $json_response;
         }
         $locale = $request->get('locale');
         $template = $request->get('template');
         if(file_exists($this->getParameter('epacks_directory').'/'.$number. '/'.$locale.'/minisite/'.$template.'/content/json/index.json')){
             $content_template = file_get_contents($this->getParameter('epacks_directory').'/'.$number. '/'.$locale.'/minisite/'.$template.'/content/json/index.json');
             $content_template = str_replace("..\\/..\\/", $this->getParameter('domain').'/uploads/'.$number.'/'.$locale. '/minisite/'.$template.'/', $content_template);
             $content_template = str_replace("../../", $this->getParameter('domain').'/uploads/'.$number.'/'.$locale. '/minisite/'.$template.'/', $content_template);
             $content_template = (array)json_decode($content_template, true);
             return new JsonResponse($content_template);
         } else {
             $json_response = new JsonResponse(['data' =>['code' => 404, 'message' => 'No file']]);
             $json_response->setStatusCode(404);
             return $json_response;
         }

     }

}



