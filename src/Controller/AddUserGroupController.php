<?php

namespace App\Controller;

use App\Entity\StatisticGroup;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class AddUserGroupController extends AbstractController
{
    /**
     * @Route("api/usertogroup", name="add_user_group")
     */
    public function index(Request $request, UserInterface $user): Response
    {
        $roles = $user->getRoles();
        if($roles[0] != 'admin'){
            return new JsonResponse(['data' => ['code' => 401, 'message' => 'No credentials']]);

        } else {
            $resource_id = $request->get('resource_id');
            $user_item = $this->getDoctrine()
                ->getRepository(StatisticGroup::class)
                ->findOneBy([
                    'resourceId' => $resource_id]);
            if($user_item){
                $userids = json_decode($request->request->get('body'), true);
                return new JsonResponse(['data' => ['code' => 404, 'message' => $request->getContent()]]);
            } else {
                return new JsonResponse(['data' => ['code' => 404, 'message' => 'No such group']]);
            }
        }
    }
}
