<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\Api\ApiResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class ListUsersController extends AbstractController
{
    /**
     * @Route(path="/api/list/users", name="admin_list_users",
     *     methods={"GET"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function getUsers(UserRepository $userRepository): Response
    {
        $users = $userRepository->findUsers();
        $response_json = ['code' => 200, 'data' => $users];
        return $this->jsonResponse($response_json);
    }

    /**
     * @param $data
     * @return JsonResponse
     */
    protected function jsonResponse(
        $data
    ): JsonResponse {
        $apiResponse = new ApiResponse($data);

        return $this->json($apiResponse);
    }
}
