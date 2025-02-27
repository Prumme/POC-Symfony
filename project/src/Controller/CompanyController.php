<?php

namespace App\Controller;

use App\Entity\Company;
use App\Form\CompanyType;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/companies')]
class CompanyController extends AbstractController
{
    #[Route('/', name: 'api_company_index', methods: ['GET'])]
    public function index(CompanyRepository $companyRepository, SerializerInterface $serializer): JsonResponse
    {
        $companies = $companyRepository->findAll();


        if (empty($companies)) {
            return new JsonResponse(['message' => 'No companies found'], Response::HTTP_NOT_FOUND);
        }

        $json = $serializer->serialize($companies, 'json', ['groups' => 'company:read']);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_company_show', methods: ['GET'])]
    public function show(Company $company, SerializerInterface $serializer): JsonResponse
    {
        $json = $serializer->serialize($company, 'json', ['groups' => 'company:read']);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/new', name: 'api_company_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || !isset($data['siret'])) {
            return new JsonResponse(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
        }

        $company = new Company();
        $company->setName($data['name']);
        $company->setSiret($data['siret']);
        if(isset($data['phone'])){
            $company->setPhone($data['phone']);
        }
        $company->setCreatedAt(new \DateTimeImmutable());
        $company->setStatusValue();

        $entityManager->persist($company);
        $entityManager->flush();

        $json = $serializer->serialize($company, 'json', ['groups' => 'company:read']);

        return new JsonResponse($json, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}/edit', name: 'api_company_update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, Company $company, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if(isset($data['name'])){
            $company->setName($data['name']);
        }
        if(isset($data['phone'])){
            $company->setPhone($data['phone']);
        }

        $em->persist($company);
        $em->flush();

        $json = $serializer->serialize($company, 'json', ['groups' => 'company:read']);

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_company_delete', methods: ['DELETE'])]
    public function delete(Company $company, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($company);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
    
}
