<?php

namespace App\Controller;

use App\Entity\StatisticGroup;
use App\Service\DTO\GroupArrayDTO;
use App\Service\DTO\GroupDTO;
use App\Traits\ValidateTrait;
use Doctrine\DBAL\Types\Types;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\SerializerInterface;

class GroupController extends AbstractController
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
     * @Route(path="/webhooks/user-group", name="add_group", methods={"POST"})
     */
    public function update(Request $request): Response
    {
        $auth_header = $request->headers->get($this->getParameter('securityHttpHeaderName'));
        if($auth_header != $this->getParameter('securityHttpHeaderValue')){
            throw new AccessDeniedHttpException('No credentials');
        }
        $groupDto = $this->deserialize($request->getContent(), GroupDTO::class, Types::JSON);
        $this->validate($groupDto);
        $response = (array)json_decode($request->getContent(), true);
        $resource_id = $response['resource_id'];
        $name = $response['name'];
        $date =  new \DateTime();
        $group_item = $this->getDoctrine()
            ->getRepository(StatisticGroup::class)
            ->findOneBy([
                'resourceId' => $resource_id]);
        if(!$group_item){
            $new_group = new StatisticGroup();
            $new_group->setName($name);
            $new_group->setResourceId($resource_id);
            $new_group->setCreatedAt($date);
            $new_group->setUpdatedAt($date);
            $em = $this->getDoctrine()->getManager();
            try {
                $em->persist($new_group);
                $em->flush();
            } catch (\Exception $e) {
                throw new BadRequestException('Username is already exists');
            }
            return new JsonResponse(['data' => ['code' => 200, 'message' => 'Group successfully added']]);
        } else {
            $group_item->setResourceId($resource_id);
            $group_item->setUpdatedAt($date);
            $group_item->setName($name);
            $em = $this->getDoctrine()->getManager();
            try {
                $em->persist($group_item);
                $em->flush();
            } catch (\Exception $e) {
                throw new BadRequestException('Username is already exists');
            }
            return new JsonResponse(['data' => ['code' => 200, 'message' => 'Group successfully updated']]);
        }
    }

    /**
     * @Route(path="/webhooks/user-group/delete-non-existent", name="delete_some_groups", methods={"DELETE"})
     */
    public function clean(Request $request): Response
    {
        $auth_header = $request->headers->get($this->getParameter('securityHttpHeaderName'));
        if($auth_header != $this->getParameter('securityHttpHeaderValue')){
            throw new AccessDeniedHttpException('No credentials');
        }
        $groupDto = $this->deserialize($request->getContent(), GroupArrayDTO::class, Types::JSON);
        $this->validate($groupDto);
        $response = (array)json_decode($request->getContent(), true);
        $resource_ids = $response['resource_ids'];
        $user_items = $this->getDoctrine()
            ->getRepository(StatisticGroup::class)
            ->findAll();
        foreach ($user_items as $group){
            $group_resource = $group->getResourceId();
            if(!in_array($group_resource, $resource_ids)) {
                $group_item = $this->getDoctrine()
                    ->getRepository(StatisticGroup::class)
                    ->findOneBy([
                        'resourceId' => $group_resource]);
                $em = $this->getDoctrine()->getManager();
                $em->remove($group_item);
                $em->flush();
            }
        }
        return new JsonResponse(['data' => ['code' => 200, 'message' => 'Groups successfully deleted']]);
    }

    /**
     * @Route(path="/webhooks/user-group/{id}", name="delete_group", methods={"DELETE"})
     */
    public function delete(string $id, Request $request): Response
    {
        $auth_header = $request->headers->get($this->getParameter('securityHttpHeaderName'));
        if($auth_header != $this->getParameter('securityHttpHeaderValue')){
            throw new AccessDeniedHttpException('No credentials');
        }
            $resource_id = htmlspecialchars($id);
            $user_item = $this->getDoctrine()
                ->getRepository(StatisticGroup::class)
                ->findOneBy([
                    'resourceId' => $resource_id]);
            if($user_item) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($user_item);
                $em->flush();
                return new JsonResponse(['data' => ['code' => 200, 'message' => 'Group successfully deleted']]);
            } else {
                throw new NotFoundHttpException('No such group');
            }
    }




}
