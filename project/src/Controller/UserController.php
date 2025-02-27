<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CompanyRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/users')]
class UserController extends AbstractController
{
    #[Route('/', name: 'api_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $users = $userRepository->findAll();
        $json = $serializer->serialize($users, 'json', ['groups' => 'user:read']);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/new', name: 'api_user_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, CompanyRepository $companyRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['firstname']) || !isset($data['email']) || !isset($data['lastname']) || !isset($data['company'])) {
            return new JsonResponse(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
        }

        $company = $companyRepository->find($data['company']);

        if (!$company) {
            return new JsonResponse(['error' => 'Company not found'], Response::HTTP_NOT_FOUND);
        }

        $user = new User();
        $user->setFirstname($data['firstname']);
        $user->setLastname($data['lastname']);
        $user->setEmail($data['email']);
        if(isset($data['phone'])){
            $user->setPhone($data['phone']);
        }
        $user->setCompany($company);
        $user->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($user);
        $entityManager->flush();

        $json = $serializer->serialize($user, 'json', ['groups' => 'user:read']);

        return new JsonResponse($json, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'api_user_show', methods: ['GET'])]
    public function show(User $user, SerializerInterface $serializer): JsonResponse
    {
        $json = $serializer->serialize($user, 'json', ['groups' => 'user:read']);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}/edit', name: 'api_user_edit', methods: ['PUT', 'PATCH'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['firstname'])) {
            $user->setFirstname($data['firstname']);
        }

        if (isset($data['lastname'])) {
            $user->setLastname($data['lastname']);
        }

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }

        if (isset($data['phone'])) {
            $user->setPhone($data['phone']);
        }

        $entityManager->persist($user);

        $entityManager->flush();

        $json = $serializer->serialize($user, 'json', ['groups' => 'user:read']);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_user_delete', methods: ['DELETE'])]
    public function delete(User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'User deleted'], Response::HTTP_NO_CONTENT);
    }
}
