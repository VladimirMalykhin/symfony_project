<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Images;
use App\Entity\UserStatisticGroup;
use App\Exception\UserException;
use App\Repository\FontsRepository;
use App\Repository\ImagesRepository;
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
use App\Infrastructure\ParametersInterface\ParametersInterface;

class AdminSaveEpackController extends AbstractController
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
     * @Route(path="/api/save/{number}", name="admin_save_epack",
     *     requirements={
     *          "number" = "%app.id_integer_regex%",
     *      },
     *     methods={"POST"})
     */
    public function index(string $number, Request $request, UserInterface $user, FontsRepository $fontsRepository, ImagesRepository $imagesRepository): Response
    {
        $roles = $user->getRoles();
        if($roles[0] != 'admin'){
            $em = $this->getDoctrine()->getManager()->createQueryBuilder();
            $dql_query = $em->select('e')
                ->from('App\Entity\Epacks', 'e');
            $group = $this->getDoctrine()
                ->getRepository(UserStatisticGroup::class)
                ->findOneBy([
                    'user' => $user->getId()]);
			if($group){
				$list_users = $this->getDoctrine()
					->getRepository(UserStatisticGroup::class)
					->findBy([
						'statisticGroup' => $group->getStatisticGroup()]);
				$users = [];
			} else {
                $users = [];
                $users[] = $user;
            }
            foreach($list_users as $user_item){
                $users[] = $user_item->getUser();
            }
            $dql_query = $dql_query->andWhere('e.user IN (:users)')
                ->andWhere('e.file = :number')
                ->setParameter(':number', $number)
                ->setParameter(':users', $users);
            $epacks = $dql_query->getQuery()->getOneOrNullResult();
        }
        else{
            $epacks = $this->getDoctrine()
                ->getRepository(Epacks::class)
                ->findOneBy([
                    'file' => $number
                ]);
        }
        if(!$epacks){
            $json_response = new JsonResponse(['data' =>['code' => 404, 'message' => 'No such E-pack']]);
            $json_response->setStatusCode(404);
            return $json_response;
        }
        $date =  new \DateTime();
        $content = json_encode($request->getContent());
        $response = (array)json_decode($request->getContent(), true);
        if($epacks->getStatus() === 0)
        {
            $new_status = 0;
        }
        if($epacks->getStatus() === 2)
        {
            $new_status = 1;
        }
        if($epacks->getStatus() === 1)
        {
            $new_status = 1;
        }
        if(!isset($response['manifest'])){
            $json_response = new JsonResponse(['data' =>['code' => 402, 'message' => 'Manifest parameter is not exist']]);
            $json_response->setStatusCode(402);
            return $json_response;
        }
        if(!isset($response['components'])){
            $json_response = new JsonResponse(['data' =>['code' => 402, 'message' => 'Components parameter is not exist']]);
            $json_response->setStatusCode(402);
            return $json_response;
        }


        $components = $response['components'];
        if(isset($components['master_template']) || isset($components['ozon_template']) || isset($components['img_template'])){
            $components_panel['ru'] = $components;
        } else {
            $components_panel = $components;
        }


        if(isset($response['usedFonts'])){
            $font_used = $fontsRepository->findFonts($response['usedFonts']);
        } else{
            $font_used = [];
        }

        foreach ($components_panel as $key => $value){
            $locale_item = new LocaleParser($key, $value, $number, $this->getParameter('base_directory'), $font_used);
            $components_panel[$key] = $locale_item->parse_constructor();
            $this->images = array_merge($this->images, $locale_item->getImages());
            $components_string = json_encode($components_panel);
            if(strpos($components_string, 'File not found')){
                return new JsonResponse(['data' => ['code' => 404, 'message' => 'Image not found']]);
            }
            if(isset($response['coverData']) && $response['coverData'] != [] && $key == 'ru'){
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
		
		$this->SetImages($components_panel);
		
		
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
        $user_item = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy([
                'username' => $manifest['author']]);
        if(!$user_item) {
            $json_response = new JsonResponse(['data' =>['code' => 404, 'message' => 'No such user']]);
            $json_response->setStatusCode(404);
            return $json_response;
        }
        $user_item = $user_item->getUserObject();
        if(isset($manifest['packageID']) && $manifest['packageID'] != ""){
        } else {
            $manifest['packageID'] = $number;
        }
        if(isset($manifest['productData']['categoriesList'])) {
            $categoriesList = [];
            $categoriesListDb = $manifest['productData']['categoriesList'];
            foreach ($manifest['productData']['categoriesList'] as $category) {
                $categoriesList[] = $category['id'];
            }
            $manifest['productData']['categoriesList'] = $categoriesList;
        }
        file_put_contents($this->getParameter('epacks_directory').'/'.$number.'/manifest.json', json_encode($manifest));
        if(isset($manifest['productData']['categoriesList'])) {
            $manifest['productData']['categoriesList'] = $categoriesListDb;
        }
        $em = $this->getDoctrine()->getManager();
        
        $imagesRepository->deleteImages($number);
        $this->SetImages();
        $epacks->setUpdatedAt($date);
        $epacks->setUpdater($user);
        $epacks->setUser($user_item);
        $epacks->setManifest(json_encode($manifest));
        $epacks->setStructure(json_encode($components_panel));
        $epacks->setMpn($manifest['productData']['MPN']);
        $epacks->setEan($manifest['productData']['EAN']);
        $epacks->setBrandname(htmlentities($manifest['productData']['brandName']));
        $epacks->setProductname($manifest['productData']['productName']);
        $epacks->setIsUpdated(false);
        $epacks->setStatus($new_status);
        
        $em->persist($epacks);
        $em->flush();
        return new JsonResponse(['data' => ['code' => 200, 'manifest' => $manifest, 'components' => $components_panel]]);


    }


    private function render_content_mvideo($contents, $locale, $epackage_number)
    {
        $content_mvideo = '';
        foreach ($contents as $content)
        {
            $pretitle_folder = '';

            $content_index = $this->prepare_data($pretitle_folder . 'index', $content, "<style>", $locale);

            if($content['template'] == 'mvideo_template')
            {
                $content_mvideo = $content_index;
            } else{
                $content_iframe = $this->prepare_data($pretitle_folder . 'iframe', $content, "<!DOCTYPE html>", $locale);
                file_put_contents($this->getParameter('epacks_directory') . '/' . $epackage_number . '/'.$locale.'/minisite/'.$content['template'].'/content/iframe/index.html', $content_iframe);
                file_put_contents($this->getParameter('epacks_directory') . '/' . $epackage_number . '/'.$locale.'/minisite/'.$content['template'].'/content/html/index.html', $content_index);
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
            $pretitle_folder = '';
            if($content['template'] == 'ali_template') $pretitle_folder = 'ali';
            $content_iframe = $this->prepare_data($pretitle_folder . 'iframe', $content, "<!DOCTYPE html>", $locale);
            //$content_iframe = $this->start_typograf($content_iframe);
            file_put_contents($this->getParameter('epacks_directory') . '/' . $epackage_number . '/'.$locale.'/minisite/'.$content['template'].'/content/iframe/index.html', $content_iframe);
            $content_index = $this->prepare_data($pretitle_folder . 'index', $content, "<style>", $locale);
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
