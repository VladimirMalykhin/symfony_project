<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserStatisticGroup;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Epacks;
use App\Service\HttpService\PanelService;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use ZipArchive;

class AdminDownloadController extends AbstractController
{
    /**
     * @Route(path="/api/download/{number}", name="admin_download",
     *     requirements={
     *          "number" = "%app.id_integer_regex%",
     *      },
     *     methods={"GET", "POST"})
     */
    public function index(UserPasswordEncoderInterface $encoder, string $number, Request $request, UserInterface $user): Response
    {
        //$plainPassword = 'adb84759';
        //$encoded = $encoder->encodePassword($user, $plainPassword);
        //return new JsonResponse(['data' => ['code' => 200, 'epack' => '/temp/archives/'.$number.'.zip']]);
		/*$epacks = $this->getDoctrine()
                       ->getRepository(Epacks::class)
                       ->findAll();*/
		/*
		$epacks = $this->getDoctrine()
                       ->getRepository(Epacks::class)
                       ->findAll();
		$em = $this->getDoctrine()->getManager();
        
		foreach($epacks as $epack){
			$structure = [];
			if(file_exists($this->getParameter('epacks_directory').'/'.$epack->getFile().'/manifest.json')){
				$locales = scandir($this->getParameter('epacks_directory').'/'.$epack->getFile());
				foreach($locales as $locale){
					if($locale != '.' && $locale != '..' && $locale != 'manifest.json'){
						$templates = scandir($this->getParameter('epacks_directory').'/'.$epack->getFile().'/'.$locale.'/minisite');
						foreach($templates as $template){
							if($template != '.' && $template != '..'){
								if(file_exists($this->getParameter('epacks_directory').'/'.$epack->getFile().'/'.$locale.'/minisite/'.$template.'/content/json/index-helper.json')){
									$structure[$locale][$template] = json_decode(file_get_contents($this->getParameter('epacks_directory').'/'.$epack->getFile().'/'.$locale.'/minisite/'.$template.'/content/json/index-helper.json'), true);
								}
							}
						}
					}
				}
				$epack->setStructure(json_encode($structure));
				$em->persist($epack);
				$em->flush();
			}
		}
		
		return new JsonResponse(['data' => ['code' => 200, 'message' => 'Ok']]);
		*/
		//$requestService = new PanelService();
		//return new JsonResponse(['data' => ['code' => 200, 'epack' => $requestService->getCategories()]]);
    	$zip_name = time();
    	$rand_name = rand(1000000, 9999999);
    	$html_elems = array();
    	$styles = '';
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
                    foreach($list_users as $user_item){
                        $users[] = $user_item->getUser();
                    }
                } else {
                    $users = [];
                    $users[] = $user;
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
            //$this->zip($this->getParameter('epacks_directory').'/'.$number, $this->getParameter('temp_directory').'/archives/'.$zip_name.$rand_name.'.zip');


		if(file_exists($this->getParameter('temp_directory').'/archives/'.$number.'.zip')){
			unlink($this->getParameter('temp_directory').'/archives/'.$number.'.zip');
		}
        $this->zip($this->getParameter('epacks_directory').'/'.$number, $this->getParameter('temp_directory').'/archives/'.$number.'.zip');


            return new JsonResponse(['data' => ['code' => 200, 'epack' => '/temp/archives/'.$number.'.zip']]);
    }

    public function zip($source, $destination)
	{
	    if (!extension_loaded('zip') || !file_exists($source)) {
	        return false;
	    }
	 
	    $zip = new ZipArchive();
	    if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
	        return false;
	    }
	 
	    $source = str_replace('\\', DIRECTORY_SEPARATOR, realpath($source));
	    $source = str_replace('/', DIRECTORY_SEPARATOR, $source);
	 
	    if (is_dir($source) === true) {
	        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source),
	            RecursiveIteratorIterator::SELF_FIRST);
	 
	        foreach ($files as $file) {
	            $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
	            $file = str_replace('/', DIRECTORY_SEPARATOR, $file);
	 
	            if ($file == '.' || $file == '..' || empty($file) || $file == DIRECTORY_SEPARATOR) {
	                continue;
	            }
	            // Ignore "." and ".." folders
	            if (in_array(substr($file, strrpos($file, DIRECTORY_SEPARATOR) + 1), array('.', '..'))) {
	                continue;
	            }
	 
	            $file = realpath($file);
	            $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
	            $file = str_replace('/', DIRECTORY_SEPARATOR, $file);
	 
	            if (is_dir($file) === true) {
	                $d = str_replace($source . DIRECTORY_SEPARATOR, '', $file);
	                if (empty($d)) {
	                    continue;
	                }
	                $zip->addEmptyDir($d);
	            } elseif (is_file($file) === true) {
                    $zip->addFile($file, str_replace($source . DIRECTORY_SEPARATOR, '', $file));
	            } else {
	                // do nothing
	            }
	        }
	    } elseif (is_file($source) === true) {
	        $zip->addFromString(basename($source), file_get_contents($source));
	    }
	    $zip->close();
	}


    public function full_copy($source, $target) {
        if (is_dir($source))  {
            @mkdir($target);
            $d = dir($source);
            while (FALSE !== ($entry = $d->read())) {
                if ($entry == '.' || $entry == '..') continue;
                $Entry = $source . '/' . $entry;
                if (is_dir($Entry)) $this->full_copy($Entry, $target . '/' . $entry);
                else copy($Entry, $target . '/' . $entry);
            }
            $d->close();
        }
        else copy($source, $target);
    }
}