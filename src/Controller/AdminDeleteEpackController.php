<?php

namespace App\Controller;

use App\Entity\UserStatisticGroup;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Security\CustomAuthenticator;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Epacks;
use ZipArchive;
use App\Repository\ImagesRepository;

class AdminDeleteEpackController extends AbstractController
{
    /**
     * @Route(path="/api/delete/{number}", name="admin_delete_epack",
     *     requirements={
     *          "number" = "%app.id_integer_regex%",
     *      },
     *     methods={"DELETE"})
     */
    public function index(string $number, Request $request, UserInterface $user, ImagesRepository $imagesRepository): Response
    {
        $roles = $user->getRoles();
        if($roles[0] != 'admin'){
            $em = $this->getDoctrine()->getManager()->createQueryBuilder();
            $dql_query = $em->select('e')
                ->from('App\Entity\Epacks', 'e');
            $group = $this->getDoctrine()
                ->getRepository(UserStatisticGroup::class)
                ->findOneBy([
                    'user' => $user->getId()]);
            if($group){
                $list_users = $this->getDoctrine()
                    ->getRepository(UserStatisticGroup::class)
                    ->findBy([
                        'statisticGroup' => $group->getStatisticGroup()]);
                $users = [];
                foreach($list_users as $user_item){
                    $users[] = $user_item->getUser();
                }
            } else {
                $users = [];
                $users[] = $user;
            }
            $dql_query = $dql_query->andWhere('e.user IN (:users)')
                ->andWhere('e.file = :number')
                ->setParameter(':number', $number)
                ->setParameter(':users', $users);
            $epacks = $dql_query->getQuery()->getOneOrNullResult();
        }
        else{
            $epacks = $this->getDoctrine()
                ->getRepository(Epacks::class)
                ->findOneBy([
                    'file' => $number]);          
        }
        if(!$epacks){
            $json_response = new JsonResponse(['data' =>['code' => 404, 'message' => 'No such E-pack']]);
            $json_response->setStatusCode(404);
            return $json_response;
        }
        $em = $this->getDoctrine()->getManager();
        $imagesRepository->deleteImages($number);
		$em->remove($epacks);
		$em->flush();
        if(is_dir($this->getParameter('epacks_directory').'/'.$number)){
            $this->emptyDir($this->getParameter('epacks_directory').'/'.$number);
        }
        else{
            $json_response = new JsonResponse(['data' =>['code' => 402, 'message' => 'System error']]);
            $json_response->setStatusCode(402);
            return $json_response;
        }

		rmdir($this->getParameter('epacks_directory').'/'.$number);
	    return new JsonResponse(['data' => ['code' => 200, 'message' => 'Epack was successfully deleted']]);
    	
    }


    function emptyDir($dir) 
    {
        if (is_dir($dir)) {
            $scn = scandir($dir);
            foreach ($scn as $files) {
                if ($files !== '.') {
                    if ($files !== '..') {
                        if (!is_dir($dir . '/' . $files)) {
                            unlink($dir . '/' . $files);
                        } else {
                            $this->emptyDir($dir . '/' . $files);
                            rmdir($dir . '/' . $files);
                        }
                    }
                }
            }
        }
    }
}
