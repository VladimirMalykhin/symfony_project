<?php

namespace App\Controller;

use App\Entity\UserStatisticGroup;
use App\Service\Api\ApiResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\EpacksRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Epacks;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use ZipArchive;
//use function App\Service\Api\Convertion\convertParameters;


class AdminListEpacksController extends AbstractController
{
    /**
     * @Route(path="/api/epacks", name="admin_list_epacks",
     *     methods={"GET"})
     *
     */

    public function index(EpacksRepository $epackFileRepository, Request $request, UserInterface $user): Response
    {

        $page = htmlspecialchars($request->get('page'));
        $limit = htmlspecialchars($request->get('limit'));
        $search = htmlspecialchars($request->get('search'));
        $roles = $user->getRoles();
        $id = $user->getId();
        $em = $this->getDoctrine()->getManager()->createQueryBuilder();
        if((!$limit || !$page) && !$search){
            $json_response = new JsonResponse(['data' =>['code' => 402, 'message' => 'Required parameters are not exist']]);
            $json_response->setStatusCode(402);
            return $json_response;
        }

        $dql_query = $em->select('e')
            ->from('App\Entity\Epacks', 'e');

        if($roles[0] != 'admin'){
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
                ->setParameter(':users', $users);
            $is_admin = false;
        } else {
            $is_admin = true;
        }
        $em_count = $epackFileRepository->createQueryBuilder('e')
            ->select('count(e.id) as count_epacks');
        if($roles[0] != 'admin'){
            $em_count->where('e.user IN (:users)')
                ->setParameter(':users', $users);
        }

        if($search != ''){
            $search = trim($search);
            $dql_query = $dql_query->setParameter('searchTerm', '%'.$search.'%');

            $dql_query = $dql_query->setParameter('searchTermDown', '%'.mb_strtolower($search).'%');
            $dql_query = $dql_query->setParameter('searchBrandDown', '%'.mb_strtolower($search).'%');
            $em_count = $em_count->setParameter('searchTerm', '%'.$search.'%');

            $em_count = $em_count->setParameter('searchTermDown', '%'.mb_strtolower($search).'%');
            $em_count = $em_count->setParameter('searchBrandDown', '%'.mb_strtolower($search).'%');

            $dql_query = $dql_query->andWhere('e.file LIKE :searchTerm OR LOWER(e.productname) LIKE :searchTermDown  OR LOWER(e.mpn) LIKE :searchTermDown OR LOWER(e.ean) LIKE :searchTermDown OR LOWER(e.brandname) LIKE :searchBrandDown');
            $em_count = $em_count->andWhere('e.file LIKE :searchTerm OR LOWER(e.productname) LIKE :searchTermDown  OR LOWER(e.mpn) LIKE :searchTermDown OR LOWER(e.ean) LIKE :searchTermDown OR LOWER(e.brandname) LIKE :searchBrandDown');
        }
        $dql_query = $dql_query->orderBy('e.createdAt', 'DESC');




         $em_count = $em_count->getQuery()->getSingleScalarResult();
        //$epacks_for_pages = $dql_query->getQuery()->getResult();
        //$count_epacks_for_pages = count($epacks_for_pages);
        if($limit != '' && $page != ''){
            $offset = $limit*($page-1);
            $dql_query = $dql_query->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        $epacks = $dql_query->getQuery()->getResult();
        $arrayCollection = array();
        $count_epacks = count($epacks);

        foreach($epacks as $item) {
            $locales = [];
            $epack_array = json_decode($item->getManifest(), true);
            $epack_structure = json_decode($item->getStructure(), true);
            foreach ($epack_structure as $locale => $localeContent){

                if(strpos($locale, 'template')){
                    foreach ($epack_structure as $templateTitle => $templateContent){
                        $locales['ru'][] = $templateTitle;
                    }
                    break;
                }
                foreach ($localeContent as $templateTitle => $templateContent){
                    $locales[$locale][] = $templateTitle;
                }

            }


            $date_updated = $item->getUpdatedAt();
            $updater = $item->getUpdater()->getUsername();
            if($is_admin == true){
                $arrayCollection[] = ['packageId' => $item->getFile(), 'name' => $epack_array['productData']['productName'], 'date_updated' => $date_updated, 'creater' => $item->getUser()->getUsername(), 'mpn' => $item->getMpn(), 'ean' => $item->getEan(), 'brandname' => $item->getBrandname(), 'updater' => $updater, 'date_created' => $item->getCreatedAt(), 'status' => $item->getStatus(), 'locales' => $locales, 'is_updated' =>$item->getIsUpdated()];
            } else {
                $arrayCollection[] = ['packageId' => $item->getFile(), 'name' => $epack_array['productData']['productName'], 'date_updated' => $date_updated, 'creater' => $item->getUser()->getUsername(), 'mpn' => $item->getMpn(), 'ean' => $item->getEan(), 'brandname' => $item->getBrandname(), 'updater' => $updater, 'date_created' => $item->getCreatedAt(), 'status' => $item->getStatus(), 'locales' => $locales];
            }

        }
        if($em_count > $limit){
            $count_pages = ceil($em_count/$limit);
       } else {
            $count_pages = 1;
       }
        return new JsonResponse(['data' => ['code' => 200, 'data' => $arrayCollection, 'nav' => ['total' => $count_pages, 'epackages' => $em_count]]]);

    }


    /*
    public function getList(EpacksRepository $epackFileRepository, Request $request, UserInterface $user) :JsonResponse
    {

        $page = htmlspecialchars($request->get('page'));
        $limit = htmlspecialchars($request->get('limit'));
        $search = htmlspecialchars($request->get('search'));
        $roles = $user->getRoles();
        $id = $user->getId();
        $em = $this->getDoctrine()->getManager()->createQueryBuilder();
        if((!$limit || !$page) && !$search){
            $json_response = new JsonResponse(['data' =>['code' => 402, 'message' => 'Required parameters are not exist']]);
            $json_response->setStatusCode(402);
            return $json_response;
        }

        $dql_query = $em->select('e')
                    ->from('App\Entity\Epacks', 'e');

        if($roles[0] != 'admin'){
            $group = $this->getDoctrine()
                ->getRepository(UserStatisticGroup::class)
                ->findOneBy([
                    'user' => $user->getId()]);
            $list_users = $this->getDoctrine()
                ->getRepository(UserStatisticGroup::class)
                ->findBy([
                    'statisticGroup' => $group->getStatisticGroup()]);
            $users = [];
            foreach($list_users as $user_item){
                $users[] = $user_item->getUser();
            }
            $dql_query = $dql_query->andWhere('e.user IN (:users)')
                ->setParameter(':users', $users);
            $is_admin = false;
            if($search != ''){
                $dql_query = $dql_query->setParameter('searchTerm', '%'.mb_strtolower($search).'%');
                $dql_query = $dql_query->setParameter('searchTermUp', '%'.mb_strtoupper($search).'%');
                $dql_query = $dql_query->setParameter('searchBrand', '%'.(binary)$search.'%');
                $dql_query = $dql_query->andWhere('e.file LIKE :searchTerm OR LOWER(e.productname) LIKE :searchTerm OR UPPER(e.productname) LIKE :searchTermUp OR LOWER(e.mpn) LIKE :searchTerm OR LOWER(e.ean) LIKE :searchTerm OR e.brandname LIKE :searchBrand');
            }
            $dql_query = $dql_query->orderBy('e.createdAt', 'DESC');
            $epacks_for_pages = $dql_query->getQuery()->getResult();
            $count_epacks_for_pages = count($epacks_for_pages);
            if($limit != '' && $page != ''){
                $offset = $limit*($page-1);
                $dql_query = $dql_query->setFirstResult($offset)
                    ->setMaxResults($limit);
            }

            $epacks = $dql_query->getQuery()->getResult();
            $arrayCollection = array();
            $count_epacks = count($epacks);

            foreach($epacks as $item) {

                $epack_array = json_decode($item->getManifest(), true);
                $date_updated = $item->getUpdatedAt();
                $updater = $item->getUpdater()->getUsername();
                if($is_admin == true){
                    $arrayCollection[] = ['packageId' => $item->getFile(), 'name' => $epack_array['productData']['productName'], 'date_updated' => $date_updated, 'creater' => $item->getUser()->getUsername(), 'mpn' => $item->getMpn(), 'ean' => $item->getEan(), 'brandname' => $item->getBrandname(), 'updater' => $updater, 'date_created' => $item->getCreatedAt(), 'status' => $item->getStatus(), 'is_updated' =>$item->getIsUpdated()];
                } else {
                    $arrayCollection[] = ['packageId' => $item->getFile(), 'name' => $epack_array['productData']['productName'], 'date_updated' => $date_updated, 'creater' => $item->getUser()->getUsername(), 'mpn' => $item->getMpn(), 'ean' => $item->getEan(), 'brandname' => $item->getBrandname(), 'updater' => $updater, 'date_created' => $item->getCreatedAt(), 'status' => $item->getStatus()];
                }

            }
            if($count_epacks_for_pages > $limit){
                $count_pages = ceil($count_epacks_for_pages/$limit);
            } else {
                $count_pages = 1;
            }
            return new JsonResponse(['data' => ['code' => 200, 'data' => $arrayCollection, 'nav' => ['total' => $count_pages]]]);
        } else {
            $is_admin = true;
            $epackages = $epackFileRepository->findByAdmin($page, $limit, $search);
            $epackages_items = (array)$epackages->getResults();
            for ($i = 0; $i < count($epackages_items); $i++)
            {
                $epackages_items[$i]['date_created'] = $epackages_items[$i]->getCreatedAt();
            }
            $epackages_items = array_map('changeDate', $epackages_items);
            //$json_response = convertParameters(json_encode($epackage_items));
            $response_json = ['code' => 200, 'data' => $epackages_items, 'nav' => ['total' => $epackages->getNumPages()]];
            return $this->jsonResponse($response_json);
        }


    }
*/

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

