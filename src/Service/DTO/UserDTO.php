<?php

namespace App\Service\DTO;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class UserDTO
{
    private string $username;
    private string $email;
    private string $password;
    private string $resource_id;

    public function __construct($username, $email, $password, $resource_id){
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->resource_id = $resource_id;
    }

    public function check_header(Request $request){
        $auth_header = $request->headers->get('AUTH_USER');
        if($auth_header !== 'admin'){
            return new JsonResponse(['data' => ['code' => 301, 'message' => 'No credentials']]);
        } else {
            return new JsonResponse(['data' => ['code' => 200, 'message' => 'All right']]);
        }
    }

    private function getRoles() : array
    {
        return $this->user->getRoles();
    }

    public function checkRoles() : bool
    {
        $roles = $this->getRoles();
        if($roles[0] == 'admin'){
            return true;
        } else {
            return false;
        }
    }
}