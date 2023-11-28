<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CloseEpackController extends AbstractController
{
    #[Route('/close/epack', name: 'app_close_epack')]
    public function index(): Response
    {
        return $this->render('close_epack/index.html.twig', [
            'controller_name' => 'CloseEpackController',
        ]);
    }
}
