<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\DTO\UserDTO;
use Doctrine\DBAL\Exception\DatabaseObjectExistsException;
use Doctrine\DBAL\Exception\DatabaseObjectNotFoundException;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\Provider\Exception\NoMappingFound;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Traits\ValidateTrait;
use Symfony\Component\Yaml\Yaml;


class UserController extends AbstractController
{
    private SerializerInterface $serializer;
    private $passwordEncoder;

    use ValidateTrait;

    public function __construct(SerializerInterface $serializer, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->serializer = $serializer;
        $this->passwordEncoder = $passwordEncoder;
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
     * @Route(path="/webhooks/user/update", name="user", methods={"POST"})
     */
    public function update(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {

        $auth_header = $request->headers->get($this->getParameter('securityHttpHeaderName'));
        if($auth_header != $this->getParameter('securityHttpHeaderValue')){
            throw new AccessDeniedHttpException('No credentials');
        }
        $userDto = $this->deserialize($request->getContent(), UserDTO::class, Types::JSON);

        $this->validate($userDto);
            $response = (array)json_decode($request->getContent(), true);
            $resource_id = $response['resource_id'];
            $username = $response['username'];
            $password = $response['password'];
            $email = $response['email'];
            $user_item = $this->getDoctrine()
                ->getRepository(User::class)
                ->findOneBy([
                    'resource_id' => $resource_id]);
            if(!$user_item){
                $user_item = new User();
                $user_item->setRoles(["ROLE_USER"]);
            }
                $user_item->setPassword($password);
                $user_item->setUsername($username);
                $user_item->setEmail($email);
                $user_item->setResourceId($resource_id);
                $em = $this->getDoctrine()->getManager();
                try {
                    $em->persist($user_item);
                    $em->flush();
                } catch (\Exception $e) {
                    throw new BadRequestException('Username is already exists');
                }

                return new JsonResponse(['data' => ['code' => 200, 'message' => 'User successfully updated']]);
    }


    /**
     * @Route(path="/webhooks/user/{id}", name="delete_user", methods={"DELETE"})
     * @param Request $request
     * @param string $id
     * @return Response
     */
    public function delete(Request $request, string $id): Response
    {
        $auth_header = $request->headers->get($this->getParameter('securityHttpHeaderName'));
        if($auth_header != $this->getParameter('securityHttpHeaderValue')){
            throw new AccessDeniedHttpException('No credentials');
        }
        $id = htmlspecialchars($id);
            $user_item = $this->getDoctrine()
                ->getRepository(User::class)
                ->findOneBy([
                    'resource_id' => $id]);
            if($user_item) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($user_item);
                $em->flush();
                return new JsonResponse(['data' => ['code' => 200, 'message' => 'User successfully deleted']]);
            } else {
                throw new NoMappingFound('No such user');
            }
    }
}
