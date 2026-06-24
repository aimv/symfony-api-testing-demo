<?php

namespace App\Controller\Api\v1;

use App\Entity\Company;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/api/v1/orders', name: 'api_v1_order_create', methods: ['POST'])]
class CreateOrderController extends AbstractController
{
    // Внедряем именованный HTTP-клиент для внешнего банка
    public function __construct(
        private readonly HttpClientInterface    $bankClient,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    // Магический метод __invoke превращает класс в Single Action Controller

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function __invoke(Request $request): JsonResponse
    {
        // 1. Получаем и декодируем JSON
        $data = json_decode($request->getContent(), true);

        // Валидация входных параметров
        if (!isset($data['company_id']) || !Uuid::isValid($data['company_id'])) {
            return new JsonResponse(
                ['error' => 'Invalid or missing company_id'],
                Response::HTTP_BAD_REQUEST
            );
        }

        if (!isset($data['amount_rub']) || !is_numeric($data['amount_rub']) || (float)$data['amount_rub'] <= 0) {
            return new JsonResponse(
                ['error' => 'Invalid or missing amount_rub'],
                Response::HTTP_BAD_REQUEST
            );
        }

        // 1. Ищем компанию в базе данных
        $companyUid = Uuid::fromString($data['company_id']);
        $company = $this->entityManager->getRepository(Company::class)->find($companyUid);
        if (!$company) {
            return new JsonResponse(['error' => 'Company not found'], Response::HTTP_NOT_FOUND);
        }

        $amountRub = (float) $data['amount_rub'];

        try {
            // 2. Запрос к внешнему банку через scoped client (base_uri настроен в framework.yaml)
            $response = $this->bankClient->request('GET', '');

            if ($response->getStatusCode() !== 200) {
                return new JsonResponse(
                    ['error' => 'External bank service error'],
                    Response::HTTP_SERVICE_UNAVAILABLE
                );
            }

            $rateData = $response->toArray();
            $eurRate = (float) $rateData['rate'];
            // Расчет суммы в Евро
            $amountEur = round($amountRub / $eurRate, 2);

            // 3. Создаем и сохраняем заказ в БД
            $order = new Order();
            $order->setCompany($company);
            $order->setAmountRub($amountRub);
            $order->setRateEur($eurRate);
            $order->setAmountEur($amountEur);

            $this->entityManager->persist($order);
            $this->entityManager->flush();

            // 4. Возвращаем ответ
            return new JsonResponse([
                'status' => 'created',
                'order_id' => $order->id->toRfc4122(), // строковый UUID созданного заказа
                'company_name' => $company->getName(),
                'amount_rub' => $order->getAmountRub(),
                'rate_eur' => $order->getRateEur(),
                'amount_eur' => $order->getAmountEur()
            ], Response::HTTP_CREATED);


        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'Internal error: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
