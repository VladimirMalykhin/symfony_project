<?php

namespace App\Controller;

use App\Entity\StatisticGroup;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class RemoveUserGroupController extends AbstractController
{
    /**
     * @Route("/remove/user/group", name="remove_user_group")
     */
    public function index(Request $request): Response
    {
        /*$roles = $user->getRoles();
        if($roles[0] != 'admin'){
            return new JsonResponse(['data' => ['code' => 401, 'message' => 'No credentials']]);

        } else {*/
            $resource_id = $request->get('resource_id');
            $user_item = $this->getDoctrine()
                ->getRepository(StatisticGroup::class)
                ->findOneBy([
                    'resourceId' => $resource_id]);
            if($user_item){
                return new JsonResponse(['data' => ['code' => 404, 'message' => $request]]);
            } else {
                return new JsonResponse(['data' => ['code' => 404, 'message' => 'No such group']]);
            }
        //}
    }
}
