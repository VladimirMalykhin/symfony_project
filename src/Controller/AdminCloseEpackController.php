<?php

namespace App\Controller;

use App\Entity\Epacks;
use App\Entity\UserStatisticGroup;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\EpackageCleaner\EpackageCleaner;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdminCloseEpackController extends AbstractController
{
    /**
     * @Route(path="/api/epack/close/{number}", name="admin_close",
     *     requirements={
     *          "number" = "%app.id_integer_regex%",
     *      },
     *     methods={"POST"})
     */
    public function index(string $number, Request $request, UserInterface $user): Response
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
                    'file' => $number
                ]);
        }
        if(!$epacks){
            throw new NotFoundHttpException('No such E-pack');
        }
		
		$components = json_decode($epacks->getStructure(), true);
		if(file_exists($this->getParameter('epacks_directory').'/'.$number.'/ru/minisite/mvideo_template/content/json/index.json')){
            $cover_data = file_get_contents($this->getParameter('epacks_directory').'/'.$number.'/ru/minisite/mvideo_template/content/json/index.json');

        }
		if(isset($cover_data)){
            $cleaning = new EpackageCleaner($number, $this->getParameter('epacks_directory'), $components, $cover_data);
        } else {
            $cleaning = new EpackageCleaner($number, $this->getParameter('epacks_directory'), $components, '');
        }
		$cleaning->clean_epackage();
		return new JsonResponse(['data' => ['code' => 200, 'message' => 'Epackage has successfylly cleaned']]);
		

    }
}
