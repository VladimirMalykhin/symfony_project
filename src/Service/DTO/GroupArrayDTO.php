<?php

namespace App\Service\DTO;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class GroupArrayDTO
{
    private array $resource_ids;

    public function __construct($resource_ids){
        $this->resource_ids = $resource_ids;
    }


}