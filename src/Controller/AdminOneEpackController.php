<?php

namespace App\Controller;

use App\Entity\UserStatisticGroup;
use App\Service\CoverService\CoverService;
use App\Service\CustomException\CustomException;
use App\Service\ResponseService\ResponseService;
use App\Service\ResponseService\Types\EpackageMvideoTypeResponse;
use App\Service\ResponseService\Types\EpackageTypeResponse;
use App\Service\ResponseService\Types\ErrorTypeResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Epacks;
use App\Entity\Images;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Repository\EpacksRepository;
use App\Repository\ImagesRepository;
use ZipArchive;


class AdminOneEpackController extends AbstractController
{


    /**
     * @Route(path="/api/epack/{number}", name="admin_one_epack",
     *     requirements={
     *          "number" = "%app.id_integer_regex%",
     *      },
     *     methods={"GET"})
     * @Security("is_granted('ROLE_USER','ROLE_ADMIN')")
    */
    public function index(string $number, UserInterface $user, EpacksRepository $epackageRepository, ImagesRepository $imagesRepository): Response
    {
        /*
        $epackage = $epackageRepository->findOneEpackage($user->getId(), $number, $user->getRoles());
        if(!$epackage){
            $response = new ResponseService(ErrorTypeResponse::class, ['code' => 404, 'message' => 'No such E-pack'], []);
            return $this->json($response->toArray(), 404);
        }
        if(file_exists($this->getParameter('epacks_directory').'/'.$number. '/ru/minisite/mvideo_template/content/json/index.json')){
            $content_cover = file_get_contents($this->getParameter('epacks_directory').'/'.$number. '/ru/minisite/mvideo_template/content/json/index.json');
            $cover= new CoverService($number, $content_cover);
            $cover_response = $cover->getCover();
        }
        $response = isset($cover_response) ? new ResponseService(EpackageMvideoTypeResponse::class, $epackage, ['coverData' => $cover_response]) : new ResponseService(EpackageTypeResponse::class, $epackage, []);
        return $this->json($response->toArray());
        */


        $response = array();
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
        if(file_exists($this->getParameter('epacks_directory').'/'.$number. '/ru/minisite/mvideo_template/content/json/index.json')){
            $cover_response = [];
            $content_cover = file_get_contents($this->getParameter('epacks_directory').'/'.$number. '/ru/minisite/mvideo_template/content/json/index.json');
            $content_cover = json_decode($content_cover, true);
            if(isset($content_cover['CoverBlock'])){
                $cover_response['srcMob'] = str_replace('../../', '/uploads/' . $number . '/ru/minisite/mvideo_template/', $content_cover['CoverBlock']['img']['mobileUrl']);
                $cover_response['srcTablet'] = str_replace('../../', '/uploads/' . $number . '/ru/minisite/mvideo_template/', $content_cover['CoverBlock']['img']['tabletUrl']);
                $cover_response['srcDesktop'] = str_replace('../../', '/uploads/' . $number . '/ru/minisite/mvideo_template/', $content_cover['CoverBlock']['img']['desktopUrl']);
                $cover_response['buttonText'] = $content_cover['CoverBlock']['buttonText'];
                $cover_response['altAttrText'] = $content_cover['CoverBlock']['altAttrText'];
            }

        }
        $manifest = json_decode($epacks->getManifest(), true);
        if(isset($manifest['packageID'])){
        } else {
            $manifest['packageID'] = $epacks->getFile();
        }

        $images = $imagesRepository->getImages($number);
		$imagesResponse = [];
		foreach($images as $image){
			$imagesResponse[] = ['url' => $image->getUrl(), 'size' => $image->getSize()];
		}
        if(isset($cover_response)){
            return new JsonResponse(['data' => ['code' => 200, 'manifest' => $manifest, 'coverData' => $cover_response, 'components' => json_decode($epacks->getStructure(), true), 'packageID' => $epacks->getFile(), 'status' => $epacks->getFile(), 'date_created' => $epacks->getCreatedAt(), 'images' => $imagesResponse]]);
        } else {
            return new JsonResponse(['data' => ['code' => 200, 'manifest' => $manifest, 'components' => json_decode($epacks->getStructure(), true), 'packageID' => $epacks->getFile(), 'status' => $epacks->getFile(), 'date_created' => $epacks->getCreatedAt(), 'images' => $imagesResponse]]);
        }

    }


}
