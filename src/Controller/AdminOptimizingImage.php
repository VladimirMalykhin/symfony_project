<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserStatisticGroup;
use App\Exception\UserException;
use App\Repository\FontsRepository;
use App\Service\EpackageCleaner\EpackageCleaner;
use App\Service\TemplateService\OzonService\OzonService;
use App\Service\TemplateParser\TemplateParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\LocaleParser\LocaleParser;
use App\Entity\Epacks;
use App\Entity\Fonts;
use App\Entity\FormatFonts;
use App\Service\Typograf\Typograf;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class AdminOptimizingImage extends AbstractController
{


    /**
     * @Route(path="/api/optimize_image/{number}", name="admin_optimize_image",
     *     requirements={
     *          "number" = "%app.id_integer_regex%",
     *      },
     *     methods={"POST"})
     */
    public function index(string $number, Request $request): Response
    {
        
        $response = (array)json_decode($request->getContent(), true);
        if(!isset($response['components'])){
            $json_response = new JsonResponse(['data' =>['code' => 402, 'message' => 'Components parameter is not exist']]);
            $json_response->setStatusCode(402);
            return $json_response;
        }

        $components= $response['components'];
        $components_new = [];
        foreach ($components as $key => $value){
            $locale_item = new LocaleParser($key, $value, $number, $this->getParameter('base_directory'), []);
            $components_new[$key] = $locale_item->parse_for_optimizing();
        }

        return new JsonResponse(['data' => ['code' => 200, 'components' => $components_new]]);

    }

}
