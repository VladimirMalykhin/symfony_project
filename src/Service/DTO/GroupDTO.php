<?php

namespace App\Service\DTO;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class GroupDTO
{
    private string $name;
    private string $resource_id;

    public function __construct($name, $resource_id){
        $this->name = $name;
        $this->resource_id = $resource_id;
    }


}