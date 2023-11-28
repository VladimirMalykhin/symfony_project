<?php

namespace App\Service\DTO;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class EpackageDTO
{
    private array $manifest;
    private array $components;

    public function __construct($manifest, $components){
        $this->manifest = $manifest;
        $this->components = $components;
    }

}