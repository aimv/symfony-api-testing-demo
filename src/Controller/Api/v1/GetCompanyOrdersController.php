<?php

namespace App\Controller\Api\v1;

use App\Entity\Company;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/v1/companies/{id}/orders', name: 'api_v1_company_orders_list', methods: ['GET'])]
class GetCompanyOrdersController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderRepository        $orderRepository
    ) {}

    public function __invoke(string $id): JsonResponse
    {
        if (!Uuid::isValid($id)) {
            return new JsonResponse(
                ['error' => 'Invalid company ID format'],
                Response::HTTP_BAD_REQUEST
            );
        }

        // Ищем компанию по объекту UID
        $companyUid = Uuid::fromString($id);
        $company = $this->entityManager->getRepository(Company::class)->find($companyUid);
        if (!$company) {
            return new JsonResponse(
                ['error' => 'Company not found'],
                Response::HTTP_NOT_FOUND);
        }

        $ordersDto = $this->orderRepository->findCompanyOrdersDto($companyUid);

        return new JsonResponse($ordersDto, Response::HTTP_OK);
    }
}
