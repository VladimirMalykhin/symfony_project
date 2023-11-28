<?php

namespace App\Controller;

use App\Entity\StatisticGroup;
use App\Entity\User;
use App\Entity\UserStatisticGroup;
use App\Service\DTO\UserGroupDTO;
use App\Traits\ValidateTrait;
use Doctrine\DBAL\Types\Types;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\SerializerInterface;

class UserGroupController extends AbstractController
{
    private SerializerInterface $serializer;
    use ValidateTrait;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @throws BadRequestException
     */
    protected function deserialize($data, string $type, string $format)
    {

        try {
            return $this->serializer->deserialize($data, $type, $format);
        } catch (NotEncodableValueException | NotNormalizableValueException $e) {
            throw new BadRequestException($e->getMessage(), Response::HTTP_BAD_REQUEST, $e);
        }
    }


    /**
     * @Route(path="/webhooks/usergroup/add", name="user_group_add", methods={"POST"})
     */
    public function add(Request $request): Response
    {
        $auth_header = $request->headers->get($this->getParameter('securityHttpHeaderName'));
        if($auth_header != $this->getParameter('securityHttpHeaderValue')){
            throw new AccessDeniedHttpException('No credentials');
        }
        $groupDto = $this->deserialize($request->getContent(), UserGroupDTO::class, Types::JSON);
        $this->validate($groupDto);
        $response = (array)json_decode($request->getContent(), true);
        $resource_id = $response['resource_id'];
        $group_item = $this->getDoctrine()
            ->getRepository(StatisticGroup::class)
            ->findOneBy([
                'resourceId' => $resource_id]);
        $user_ids = $response['userIds'];
        foreach ($user_ids as $user_id){
            $user_item = $this->getDoctrine()
                ->getRepository(User::class)
                ->findOneBy([
                    'resource_id' => $user_id]);
            if($user_item){
                $new_group = new UserStatisticGroup();
                $new_group->setStatisticGroup($group_item);
                $new_group->setUser($user_item);
                $em = $this->getDoctrine()->getManager();
                $em->persist($new_group);
                $em->flush();
            }
            

        }
        return new JsonResponse(['data' => ['code' => 200, 'message' => 'Users to group successfully added']]);
    }

    /**
     * @Route(path="/webhooks/usergroup/delete", name="user_group_delete", methods={"PUT"})
     */
    public function delete(Request $request): Response
    {
        $auth_header = $request->headers->get($this->getParameter('securityHttpHeaderName'));
        if($auth_header != $this->getParameter('securityHttpHeaderValue')){
            throw new AccessDeniedHttpException('No credentials');
        }
        $groupDto = $this->deserialize($request->getContent(), UserGroupDTO::class, Types::JSON);
        $this->validate($groupDto);
        $response = (array)json_decode($request->getContent(), true);
        $resource_id = $response['resource_id'];
        $group_item = $this->getDoctrine()
            ->getRepository(StatisticGroup::class)
            ->findOneBy([
                'resourceId' => $resource_id]);
        $user_ids = $response['userIds'];
        foreach ($user_ids as $user_id){
            $user_item = $this->getDoctrine()
                ->getRepository(User::class)
                ->findOneBy([
                    'resource_id' => $user_id]);
            $user_group_item = $this->getDoctrine()
                ->getRepository(UserStatisticGroup::class)
                ->findBy([
                    'statisticGroup' => $group_item,
                    'user' => $user_item]);
            $em = $this->getDoctrine()->getManager();
            foreach ($user_group_item as $item_content){
                $em->remove($item_content);
                $em->flush();
            }

        }
        return new JsonResponse(['data' => ['code' => 200, 'message' => 'Users from group successfully deleted']]);
    }

    /**
     * @Route(path="/webhooks/usergroup/update", name="user_group_update", methods={"PUT"})
     */
    public function update(Request $request): Response
    {
        $auth_header = $request->headers->get($this->getParameter('securityHttpHeaderName'));
        if($auth_header != $this->getParameter('securityHttpHeaderValue')){
            throw new AccessDeniedHttpException('No credentials');
        }
        $groupDto = $this->deserialize($request->getContent(), UserGroupDTO::class, Types::JSON);
        $this->validate($groupDto);
        $response = (array)json_decode($request->getContent(), true);
        $resource_id = $response['resource_id'];
        $em = $this->getDoctrine()->getManager();
        $group_item = $this->getDoctrine()
            ->getRepository(StatisticGroup::class)
            ->findOneBy([
                'resourceId' => $resource_id]);
        $user_group_item = $this->getDoctrine()
            ->getRepository(UserStatisticGroup::class)
            ->findBy([
                'statisticGroup' => $group_item]);
        foreach ($user_group_item as $item_content){
            $em->remove($item_content);
            $em->flush();
        }
        $user_ids = $response['userIds'];
        foreach ($user_ids as $user_id){
            $user_item = $this->getDoctrine()
                ->getRepository(User::class)
                ->findOneBy([
                    'resource_id' => $user_id]);
            if($user_item){
                $new_group = new UserStatisticGroup();
                $new_group->setStatisticGroup($group_item);
                $new_group->setUser($user_item);
                $em = $this->getDoctrine()->getManager();
                $em->persist($new_group);
                $em->flush();
            }
            

        }
        return new JsonResponse(['data' => ['code' => 200, 'message' => 'Group successfully updated']]);

    }
}
