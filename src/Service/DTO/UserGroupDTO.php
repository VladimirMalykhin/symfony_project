<?php

namespace App\Service\DTO;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class UserGroupDTO
{
    private array $userIds;
    private string $resource_id;

    public function __construct($userIds, $resource_id){
        $this->userIds = $userIds;
        $this->resource_id = $resource_id;
    }


}