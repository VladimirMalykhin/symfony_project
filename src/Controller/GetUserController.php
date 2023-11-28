<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class GetUserController extends AbstractController
{
    /**
     * @Route(path="api/user/{login}", name="get_user", methods={"GET"})
     */
    public function index(string $login): Response
    {
        $login = htmlspecialchars($login);
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy([
                'username' => $login]);
        if(!$user){
            $json_response = new JsonResponse(['data' =>['code' => 404, 'message' => 'No such user']]);
            $json_response->setStatusCode(404);
            return $json_response;
        }

        return new JsonResponse(['data' => ['code' => 200, 'roles' => $user->getRoles()]]);
    }
}
