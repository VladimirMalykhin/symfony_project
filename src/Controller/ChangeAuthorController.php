<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class ChangeAuthorController extends AbstractController
{
    /**
     * @Route(path="/api/change-author/{number}", name="admin_change_author_epacks",
     *     methods={"POST"})
     *
     * requirements={
     *          "number" = "%app.id_integer_regex%",
     * },
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function index(string $number, Request $request, UserRepository $userRepository): Response
    {
        $em = $this->getDoctrine()->getManager()->createQueryBuilder();
        $user_id = $request->get('user');
        //$user = $userRepository->findOneUser($user_id)->getUserObject();
        $user_item = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy([
                'id' => $user_id]);
        $user = $user_item->getUserObject();
        if($user){
            $dql_query = $em->select('e')
                ->from('App\Entity\Epacks', 'e')
                ->where('e.file = :number')
                ->setParameter(':number', $number);
            $epack = $dql_query->getQuery()->getOneOrNullResult();
            if($epack){
                $manifest = json_decode($epack->getManifest(), true);
                $manifest['author'] = $user->getUsername();
                $epack->setManifest(json_encode($manifest));
                file_put_contents($this->getParameter('epacks_directory').'/'.$number.'/manifest.json', json_encode($manifest));
                $epack->setUser($user);
                $em = $this->getDoctrine()->getManager();
                $em->persist($epack);
                $em->flush();
                $json_response = new JsonResponse(['data' =>['code' => 200, 'message' => 'Author is successfully updated']]);
                return $json_response;
            } else {
                $json_response = new JsonResponse(['data' =>['code' => 404, 'message' => 'No such epackage']]);
                $json_response->setStatusCode(404);
                return $json_response;
            }
        } else {
            $json_response = new JsonResponse(['data' =>['code' => 404, 'message' => 'No such user']]);
            $json_response->setStatusCode(404);
            return $json_response;
        }
    }
}
