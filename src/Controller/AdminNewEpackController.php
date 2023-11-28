<?php

namespace App\Controller;

use App\Service\UploadingParser\UploadingParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Security\CustomAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Service\HttpService\PanelService;
use App\Entity\Epacks;
use App\Entity\Fonts;
use App\Entity\FormatFonts;
use ZipArchive;

class AdminNewEpackController extends AbstractController
{
    /**
     * @Route(path="/api/new/epack", name="admin_new_epack",
     *     requirements={
     *         "number" = "%app.id_integer_regex%",
     *     },
     *      methods={"POST"})
     */
    public function index(Request $request, UserInterface $user): Response
    {
        $requestService = new PanelService();
        $categoriesPanel = $requestService->getCategories();
        $time_var = time();
        $date =  new \DateTime();
    	$username = $user->getUsername();
        $rand_name = rand(1000000, 9999999);
        $number = htmlspecialchars($request->get('number'));
        if($number != ''){
            $em = $this->getDoctrine()->getManager()->createQueryBuilder();
            $dql_query = $em->select('e')
                ->from('App\Entity\Epacks', 'e')
                ->where('e.file = :number')
                ->setParameter(':number', $number);
            $epack = $dql_query->getQuery()->getOneOrNullResult();
            if($epack){
                $json_response = new JsonResponse(['data' =>['code' => 402, 'message' => 'Number of Epackage is already exists']]);
                $json_response->setStatusCode(402);
                return $json_response;
            } else {
                $filename = $number;
            }
        } else {
            $filename = $time_var.$rand_name;
        }


        $date =  new \DateTime();
        $file = $request->files->get('file');
        if($file != null){
            if($file->guessExtension() != 'zip'){
                $json_response = new JsonResponse(['data' =>['code' => 402, 'message' => 'Wrong type of data, Zip must be']]);
                $json_response->setStatusCode(402);
                return $json_response;
            }
        } else {
            $json_response = new JsonResponse(['data' =>['code' => 402, 'message' => 'Error, please upload another file']]);
            $json_response->setStatusCode(402);
            return $json_response;
        }
        mkdir($this->getParameter('epacks_directory').'/'.$filename);
        $file->move($this->getParameter('epacks_directory').'/'.$filename, $filename.'.zip');
        $zip = new ZipArchive();
	    	$zipname = $this->getParameter('epacks_directory').'/'.$filename.'/'.$filename.'.zip';
            $zip->open($this->getParameter('epacks_directory').'/'.$filename.'/'.$filename.'.zip');
	    	$zip->extractTo($this->getParameter('epacks_directory').'/'.$filename);
            $zip->close();
	        $zip->open($zipname);
            if(!is_file($this->getParameter('epacks_directory').'/'.$filename.'/manifest.json')){
                return new JsonResponse(['data' =>['code' => 402, 'message' => 'Wrong structure of epack']]);
            }
	        $manifest_content = file_get_contents($this->getParameter('epacks_directory').'/'.$filename.'/manifest.json');
	        $epack_array = json_decode($manifest_content, true);
            $old_id = $epack_array['packageID'];
	        $epack_array['packageID'] = $filename;
            $epack_array['author'] = $username;
            $epack_array['creationDate'] = $date;
            if(isset($epack_array['productData']['categoriesList'])){
                $categoriesList = [];
                $categoriesListDb = $epack_array['productData']['categoriesList'];
                foreach ($epack_array['productData']['categoriesList'] as $category){
                    $categoriesList[] = $category;
                }
                $epack_array['productData']['categoriesList'] = $categoriesList;
            }


            file_put_contents($this->getParameter('epacks_directory').'/'.$filename.'/manifest.json', json_encode($epack_array));
            if(isset($epack_array['productData']['categoriesList'])){
                $categoriesDb = [];
                foreach($categoriesListDb as $category){
                    $categoryKey = array_search($category, array_column($categoriesPanel, 'id'));
                    if($categoryKey) $categoriesDb[] = ['id' => $categoriesPanel[$categoryKey]['id'], 'parentId' => $categoriesPanel[$categoryKey]['parentId'], 'label' => $categoriesPanel[$categoryKey]['name']];
                    
                }
                $epack_array['productData']['categoriesList'] = $categoriesDb;
            }
            $epackage_locales = scandir($this->getParameter('epacks_directory').'/'.$filename);
            $components = [];
            foreach($epackage_locales as $locale)
            {
                if ($locale != '.' && $locale != '..' && $locale != 'manifest.json' && $locale != $filename.'.zip' && $locale != '')
                {
                    $components[$locale] = [];
                    $epackage_templates = scandir($this->getParameter('epacks_directory').'/'.$filename.'/'.$locale.'/minisite/');
					
                    foreach ($epackage_templates as $template)
                    {
                        if ($template != '.' && $template != '..')
                        {
                            $components[$locale][$template] = [];
                            if(is_file($this->getParameter('epacks_directory').'/'.$filename.'/'.$locale.'/minisite/'.$template.'/content/json/index-helper.json')){
                                $json_content = file_get_contents($this->getParameter('epacks_directory').'/'.$filename.'/'.$locale.'/minisite/'.$template.'/content/json/index-helper.json');
                                $json_content = str_replace($old_id, $filename, $json_content);
								
                                $uploading_parser = new UploadingParser(json_decode($json_content, true));
                                $json_content = $uploading_parser->parse_upload();
                                file_put_contents($this->getParameter('epacks_directory').'/'.$filename.'/'.$locale.'/minisite/'.$template.'/content/json/index-helper.json', json_encode($json_content));
                                $components[$locale][$template] = $json_content;
                                if(is_file($this->getParameter('epacks_directory').'/'.$filename.'/'.$locale.'/minisite/'.$template.'/content/iframe/index.html')){
                                    $iframe_content = file_get_contents($this->getParameter('epacks_directory').'/'.$filename.'/'.$locale.'/minisite/'.$template.'/content/iframe/index.html');
                                    $iframe_content = str_replace($old_id, $filename, $iframe_content);

                                    file_put_contents($this->getParameter('epacks_directory').'/'.$filename.'/'.$locale.'/minisite/'.$template.'/content/iframe/index.html', $iframe_content);
                                }
                                if(is_file($this->getParameter('epacks_directory').'/'.$filename.'/'.$locale.'/minisite/'.$template.'/content/index/index.html')){
                                    $index_content = file_get_contents($this->getParameter('epacks_directory').'/'.$filename.'/'.$locale.'/minisite/'.$template.'/content/index/index.html');
                                    $index_content = str_replace($old_id, $filename, $index_content);

                                    file_put_contents($this->getParameter('epacks_directory').'/'.$filename.'/'.$locale.'/minisite/'.$template.'/content/index/index.html', $index_content);
                                }
                                if(is_file($this->getParameter('epacks_directory').'/'.$filename.'/'.$locale.'/minisite/'.$template.'/content/json/index.json')){
                                    $json_content2 = file_get_contents($this->getParameter('epacks_directory').'/'.$filename.'/'.$locale.'/minisite/'.$template.'/content/json/index.json');
                                    $json_content2 = str_replace($old_id, $filename, $json_content2);
                                    if($template != 'ozon_template') {
                                        $uploading_parser = new UploadingParser(json_decode($json_content2, true));
                                        $json_content2 = $uploading_parser->parse_upload_epackage();
                                    }
                                    file_put_contents($this->getParameter('epacks_directory').'/'.$filename.'/'.$locale.'/minisite/'.$template.'/content/json/index.json', json_encode($json_content2));

                                }
                            }
                        }

                    }
                }
            }



            $manifest = json_encode($epack_array);
            $components_db = json_encode($components);



            $epack = new Epacks();
            $epack->setFile($filename);
            $epack->setUser($user);
            $epack->setUpdater($user);
            $epack->setCreatedAt($date);
	    $epack->setManifest($manifest);
            $epack->setStructure($components_db);
            $manifest = json_decode($manifest, true);
            $epack->setMpn($manifest['productData']['MPN']);
            $epack->setEan($manifest['productData']['EAN']);
            $epack->setBrandname($manifest['productData']['brandName']);
            $epack->setProductname($manifest['productData']['productName']);
            $epack->setUpdatedAt($date);
            $epack->setIsUpdated(false);
            $epack->setStatus(0);
            $em = $this->getDoctrine()->getManager();
            $em->persist($epack);
            $em->flush();
            unlink($this->getParameter('epacks_directory').'/'.$filename.'/'.$filename.'.zip');

                return new JsonResponse(['data' => ['code' => 200, 'manifest' => $epack_array, 'components' => $components]]);


    }
}


