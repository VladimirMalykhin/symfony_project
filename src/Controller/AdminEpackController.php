<?php

namespace App\Controller;

use App\Entity\User;

use App\Repository\EpacksRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Service\Security\WebServiceUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


class AdminEpackController extends AbstractController
{
    /**
     * @Route(path="/api/epackage_author", name="admin_author_epack",
     *
     *     methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
     public function index(EpacksRepository $epackFileRepository, Request $request): Response
     {
         $params = json_decode($request->getContent(), true);
         if(!isset($params['author']) || !isset($params['epackages'])){
             $json_response = new JsonResponse(['data' =>['code' => 402, 'message' => 'Wrong structure']]);
             $json_response->setStatusCode(402);
             return $json_response;
         }
         $user_item = $this->getDoctrine()
             ->getRepository(User::class)
             ->findOneBy([
                 'username' => $params['author']]);
         if(!$user_item) {
             $json_response = new JsonResponse(['data' =>['code' => 404, 'message' => 'No such user']]);
             $json_response->setStatusCode(404);
             return $json_response;
         }
         $epacks = $epackFileRepository->createQueryBuilder('a')
             ->select('a')
             ->where('a.file IN (:files)')->setParameter('files', $params['epackages'])
             ->getQuery()->getResult();

         if(!$epacks){
             $json_response = new JsonResponse(['data' =>['code' => 404, 'message' => 'No such E-pack']]);
             $json_response->setStatusCode(404);
             return $json_response;
         }
         foreach ($epacks as $epack){
            $manifest = json_decode($epack->getManifest(), true);
            $manifest['author'] = $user_item->getUsername();
            $epack->setManifest(json_encode($manifest));
            file_put_contents($this->getParameter('epacks_directory').'/'.$epack->getFile().'/manifest.json', json_encode($manifest));
             $epack->setUser($user_item);
             $em = $this->getDoctrine()->getManager();
             $em->persist($epack);
             $em->flush();
         }

         return new JsonResponse(['data' => ['code' => 200, 'message' => 'Автор изменен']]);

     }

}



