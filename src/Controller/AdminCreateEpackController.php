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
use App\Entity\Epacks;
use App\Entity\Fonts;
use App\Entity\FormatFonts;
use App\Repository\ImagesRepository;
use App\Entity\Images;

class AdminCreateEpackController extends AbstractController
{

    private $remoteTypograf;
    private array $images = [];

    public function __construct(){
        $this->remoteTypograf = new Typograf('utf-8');
        $this->remoteTypograf = new Typograf('utf-8');
        $this->remoteTypograf->htmlEntities();
        $this->remoteTypograf->br (false);
        $this->remoteTypograf->p (true);
        $this->remoteTypograf->nobr (3);
        $this->remoteTypograf->quotA ('laquo raquo');
        $this->remoteTypograf->quotB ('bdquo ldquo');
    }

    /**
     * @Route(path="/api/create", name="admin_create_epack", methods={"POST"})
     */
    public function index(Request $request, UserInterface $user, FontsRepository $fontsRepository, ImagesRepository $imagesRepository): Response
    {
        $date =  new \DateTime();
        $time_var = time();
        $rand_name = rand(1000000, 9999999);
        $epack_number = $number = $time_var.$rand_name;
        $content = json_encode($request->getContent());
        $response = (array)json_decode($request->getContent(), true);
        if(!isset($response['manifest'])){
            return new JsonResponse(['data' =>['code' => 402, 'message' => 'Manifest parameter is not exist']]);
        }
        if(!isset($response['components'])){
            return new JsonResponse(['data' =>['code' => 402, 'message' => 'Components parameter is not exist']]);
        }
        $manifest = $response['manifest'];
		/*
		$epackageEan = $this->getDoctrine()
            ->getRepository(Epacks::class)
            ->findOneBy([
                'ean' => $manifest['productData']['EAN']]);
				
		$epackageMpn = $this->getDoctrine()
            ->getRepository(Epacks::class)
            ->findOneBy([
                'mpn' => $manifest['productData']['MPN']]);
				
		if($epackageEan && $epackageEan->getFile() != $epacks->getFile()){
			$json_response = new JsonResponse(['data' =>['code' => 402, 'message' => 'EAN must be unique']]);
            $json_response->setStatusCode(402);
            return $json_response;
		}			
		if($epackageMpn && $epackageMpn->getFile() != $epacks->getFile()) {
			$json_response = new JsonResponse(['data' =>['code' => 402, 'message' => 'MPN must be unique']]);
            $json_response->setStatusCode(402);
            return $json_response;
		}
		*/
        mkdir($this->getParameter('epacks_directory').'/'.$number);
        $components_panel = $response['components'];


        if(isset($response['usedFonts'])){
            $font_used = $fontsRepository->findFonts($response['usedFonts']);
        } else{
            $font_used = [];
        }
        $components_epackage = [];

        foreach ($components_panel as $key => $value){
            $locale_item = new LocaleParser($key, $value, $number, $this->getParameter('base_directory'), $font_used);
            $components_epackage[$key] = $locale_item->parse_constructor();
            $this->images = array_merge($this->images, $locale_item->getImages());
            $components_string = json_encode($components_panel);
            if(strpos($components_string, 'File not found')){
                return new JsonResponse(['data' => ['code' => 404, 'message' => 'Image not found']]);
            }
            if(isset($response['coverData']) && $response['coverData'] != []){
                $cover_item = $locale_item->parse_cover($response['coverData']);
                $cover_string = json_encode($cover_item);
                if(strpos($cover_string, 'File not found')){
                    return new JsonResponse(['data' => ['code' => 404, 'message' => 'Image not found']]);
                }

            }
            $components_epackage = $components_panel;
            $components_epackage[$key] = $locale_item->parse_epackage();
            $render_content = $locale_item->getRenderContent();
            if(isset($response['coverData']) && $response['coverData'] != [] && $key == 'ru') {
                $content_mvideo = $this->render_content_mvideo($render_content, $key, $number);
            } else {
                $this->render_content($render_content, $key, $number);
            }
            if(isset($response['coverData']) && $response['coverData'] != [] && $key == 'ru') {
                $locale_item->parse_cover_epackage($cover_item, $content_mvideo);
            }
        }

        foreach ($components_epackage as $key => $value){
            $locale_item = new LocaleParser($key, $value, $number, $this->getParameter('base_directory'), $font_used);
            $components_epackage[$key] = $locale_item->parse_epackage();
            $render_content = $locale_item->getRenderContent();
            $this->render_content($render_content, $key, $number);
        }


		
        $manifest['packageID'] = $epack_number;
        $manifest['author'] = $user->getUsername();
        $manifest['creationDate'] = $date->format('d-m-Y H:i:s');
        $manifest_string = json_encode($manifest);
        if(isset($manifest['productData']['categoriesList'])){
            $categoriesList = [];
            $categoriesListDb = $manifest['productData']['categoriesList'];
            foreach ($manifest['productData']['categoriesList'] as $category){
                $categoriesList[] = $category['id'];
            }
            $manifest['productData']['categoriesList'] = $categoriesList;
        }

        file_put_contents($this->getParameter('epacks_directory').'/'.$number.'/manifest.json', json_encode($manifest));
        if(isset($manifest['productData']['categoriesList'])) {
            $manifest['productData']['categoriesList'] = $categoriesListDb;
        }


        $this->SetImages();

        $epack = new Epacks();
        $epack->setFile($number);
        $epack->setUser($user);
        $epack->setUpdater($user);
        $epack->setManifest($manifest_string);
        $epack->setStructure($components_string);
        $epack->setCreatedAt($date);
        $epack->setMpn($manifest['productData']['MPN']);
        $epack->setEan($manifest['productData']['EAN']);
        $epack->setBrandname(htmlentities($manifest['productData']['brandName']));
        $epack->setProductname($manifest['productData']['productName']);
        $epack->setUpdatedAt($date);
        $epack->setIsUpdated(false);
        $epack->setStatus(0);
        $em = $this->getDoctrine()->getManager();
        $em->persist($epack);
        $em->flush();

        return new JsonResponse(['data' => ['code' => 200, 'manifest' => $manifest, 'components' => $components_panel]]);
    }


    private function render_content_mvideo($contents, $locale, $epackage_number)
    {
        $content_mvideo = '';
        foreach ($contents as $content)
        {
            $pretitle_folder = '';
            //$content_iframe = $this->prepare_data($pretitle_folder . 'iframe', $content, "<!DOCTYPE html>");
            //file_put_contents($this->getParameter('epacks_directory') . '/' . $epackage_number . '/'.$locale.'/minisite/'.$content['template'].'/content/iframe/index.html', $content_iframe);
            $content_index = $this->prepare_data($pretitle_folder . 'index', $content, "<style>", $locale);
            file_put_contents($this->getParameter('epacks_directory') . '/' . $epackage_number . '/'.$locale.'/minisite/'.$content['template'].'/content/html/index.html', $content_index);
            if($content['template'] == 'mvideo_template')
            {
                $content_mvideo = $content_index;

            }
        }
        return $content_mvideo;
    }


    private function SetImages(){
        foreach($this->images as $image){
            $epack = new Images();
            $epack->setEpackage($image['epackage']);
            $epack->setUrl($image['url']);
            $epack->setSize($image['size']);
            $em = $this->getDoctrine()->getManager();
            $em->persist($epack);
            $em->flush();
        }

	}


    private function start_typograf($text) :string
    {
        return $this->remoteTypograf->processText ($text);
    }


    private function render_content($contents, $locale, $epackage_number)
    {
        foreach ($contents as $content)
        {
            $content_iframe = $this->prepare_data('iframe', $content, "<!DOCTYPE html>", $locale);
            file_put_contents($this->getParameter('epacks_directory') . '/' . $epackage_number . '/'.$locale.'/minisite/'.$content['template'].'/content/iframe/index.html', $content_iframe);
            //$content_iframe = $this->start_typograf($content_iframe);
            $content_index = $this->prepare_data('index', $content, "<style>", $locale);
            //$content_index = $this->start_typograf($content_index);
            file_put_contents($this->getParameter('epacks_directory') . '/' . $epackage_number . '/'.$locale.'/minisite/'.$content['template'].'/content/html/index.html', $content_index);
        }
    }


    private function prepare_data($folder_name, $content, $exploded_symbol, $locale) :string
    {
        if(strpos($locale, '_ar')){
            $html_content = $this->render('admin_download/ar'.$folder_name.'.html.twig', [
                'components' => $content['content'],
                'styles' => $content['styles']
            ]);
        } else {
            $html_content = $this->render('admin_download/'.$folder_name.'.html.twig', [
                'components' => $content['content'],
                'styles' => $content['styles']
            ]);
        }
        $html_content = str_replace('&quot;', '"', $html_content);
        $html_content = str_replace("&#039;", "'", $html_content);
        $html_content = explode($exploded_symbol, $html_content);
        return $exploded_symbol . $html_content[1];
    }

}
