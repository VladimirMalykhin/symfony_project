<?php

namespace App\Controller;

use App\Entity\Epacks;
use App\Entity\UserStatisticGroup;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class AdminStatusController extends AbstractController
{
    /**
     * @Route(path="/api/status/{number}", name="admin_status",
     *     requirements={
     *          "number" = "%app.id_integer_regex%",
     *      },
     *     methods={"POST"});
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function index(string $number, Request $request, UserInterface $user): Response
    {
        $response = (array)json_decode($request->getContent(), true);
            $number = htmlspecialchars($number);

                $epacks = $this->getDoctrine()
                    ->getRepository(Epacks::class)
                    ->findOneBy([
                        'file' => $number
                    ]);
                if(!$epacks){
                    $json_response = new JsonResponse(['data' =>['code' => 404, 'message' => 'No such E-pack']]);
                    $json_response->setStatusCode(404);
                    return $json_response;
                }
                $epacks->setIsUpdated($response['status']);
                $em = $this->getDoctrine()->getManager();
                $em->persist($epacks);
                $em->flush();
                $json_response = new JsonResponse(['data' => ['code' => 200, 'message' => 'Status successfully updated']]);
                $json_response->setStatusCode(200);
                return $json_response;

    }
}
