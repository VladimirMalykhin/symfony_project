<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\FontsRepository;
use App\Entity\Fonts;
use App\Entity\FormatFonts;

class AdminFontsConrollerController extends AbstractController
{
    /**
     * @Route(path="/api/fonts", name="admin_fonts_conroller", methods={"GET"})
     */
    public function index(FontsRepository $FontsRepository): Response
    {
    	
    	$arrayCollection = array();
        $font_object = array();
        
    	$fonts = $this->getDoctrine()
                   ->getRepository(Fonts::class)
                   ->findAll();
        $arrayCollection = [];
        foreach ($fonts as $font) {
            $formats = $this->getDoctrine()
                   ->getRepository(FormatFonts::class)
                   ->findBy(['font' => $font]);
            $font_object['fontFamily'] = $font->getFontFamily();
            $font_object['fontWeight'] = $font->getFontWeight();
            $font_object['fontStyle'] = $font->getFontStyle();
	    $formats_object = array();
	    foreach ($formats as $format) {
                $one_format = array();
                $one_format['format'] = $format->getTitle();
		$one_format['url'] = $format->getUrl();
                $formats_object[] = $one_format;
            }
	    $font_object['fonts'] = $formats_object;

           
            
            $arrayCollection[] = $font_object;
        }
        return new JsonResponse(['data' => ['code' => 200, 'fonts' => $arrayCollection]]);
    }
}
