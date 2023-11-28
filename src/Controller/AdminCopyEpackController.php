<?php

namespace App\Controller;

use App\Entity\UserStatisticGroup;
use App\Repository\EpacksRepository;
use App\Service\LocaleParser\LocaleParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Epacks;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Security\CustomAuthenticator;
use ZipArchive;
use App\Repository\ImagesRepository;

class AdminCopyEpackController extends AbstractController
{
    /**
     * @Route(path="/api/copy/{number}", name="admin_copy_epack",
     *     requirements={
     *          "number" = "%app.id_integer_regex%",
     *      },
     *     methods={"COPY", "POST"})
     */
    public function index(string $number, Request $request, UserInterface $user, EpacksRepository $epacksRepository, ImagesRepository $imagesRepository): Response
    {
        //$epackage = $epacksRepository->findOneEpackage($user->getId(), $number);
        //return new JsonResponse(['data' => ['code' => 200, 'components' => $epackage]]);

        $params = array('Picture');

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
	            if(!$epacks){
                    $json_response = new JsonResponse(['data' =>['code' => 404, 'message' => 'No such E-pack']]);
                    $json_response->setStatusCode(404);
                    return $json_response;
	            }
	        }
	        else{
                $epacks = $this->getDoctrine()
                       ->getRepository(Epacks::class)
                       ->findOneBy([
                        'file' => $number
                        ]);
            }
            if(!$epacks){
                $json_response = new JsonResponse(['data' =>['code' => 402, 'message' => 'Wrong number of E-pack']]);
                $json_response->setStatusCode(402);
                return $json_response;
            }
	        $date =  new \DateTime();
	    	$old = $this->getParameter('epacks_directory').'/'.$number;
	    	$time_var = time();
        	$rand_name = rand(1000000, 9999999);
	    	$new_name = $time_var.$rand_name;
	    	$new = $this->getParameter('epacks_directory').'/'.$new_name;
            mkdir($this->getParameter('epacks_directory').'/'.$new_name);
	    	$this->full_copy($old, $new);
	    	if(!is_file($this->getParameter('epacks_directory').'/'.$new_name.'/manifest.json')){
	    		//return new JsonResponse(['data' => ['code' => 500, 'message' => 'System error']]);
	    	}
            $manifest = $epacks->getManifest();
            $components = $epacks->getStructure();
            $components = str_replace($number, $new_name, $components);
            $manifest = json_decode($manifest, true);
            $manifest['packageID'] = $new_name;
        	$manifest['author'] = $user->getUsername();
        	$manifest['creationDate'] = $date;
        	$number_str = (string)$number;
        	$new_name_str = (string)$new_name;
        	$components = json_decode($components, true);
            $components2 = [];

            $content_manifest = file_get_contents($this->getParameter('epacks_directory').'/'.$new_name.'/manifest.json');
            if(isset($components['master_template']) || isset($components['ozon_template']) || isset($components['img_template'])){
                $components2['ru'] = $components;
                $components = $components2;
            }
            foreach ($components as $locale => $component)
            {
                $locale_item = new LocaleParser($locale, $component, $number, $this->getParameter('base_directory'), []);
                $locale_item->parse_copy($new_name);
            }
            
            $content_manifest = str_replace($number, $new_name, $content_manifest);
        	file_put_contents($this->getParameter('epacks_directory').'/'.$new_name.'/manifest.json', $content_manifest);
            $epack = new Epacks();
	        $epack->setFile($new_name);
	        $epack->setUser($user);
	        $epack->setUpdater($user);
	        $epack->setManifest(json_encode($manifest));
            $epack->setStructure(json_encode($components));
            $epack->setCreatedAt($date);
            $epack->setMpn($epacks->getMpn());
            $epack->setEan($epacks->getEan());
            $epack->setBrandname($epacks->getBrandname());
            $epack->setProductname($epacks->getProductname());
            $epack->setUpdatedAt($date);
            $epack->setIsUpdated(false);
            $epack->setStatus(0);
	        $em = $this->getDoctrine()->getManager();
	        $em->persist($epack);
	        $em->flush();
	        return new JsonResponse(['data' => ['code' => 200, 'manifest' => $manifest, 'components' => $components]]);

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
