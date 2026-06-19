<?php

namespace App\Controller\Api\v1;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/api/v1/orders', name: 'api_v1_order_create', methods: ['POST'])]
class CreateOrderController extends AbstractController
{
    // Внедряем именованный HTTP-клиент для внешнего банка
    public function __construct(
        private HttpClientInterface $bankClient
    ) {}

    // Магический метод __invoke превращает класс в Single Action Controller
    public function __invoke(Request $request): JsonResponse
    {
        // 1. Получаем и декодируем JSON
        $data = json_decode($request->getContent(), true);

        if (!isset($data['amount_rub']) || !is_numeric($data['amount_rub'])) {
            return new JsonResponse(
                ['error' => 'Invalid or missing amount_rub'],
                Response::HTTP_BAD_REQUEST
            );
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

            // 3. Расчет суммы в Евро
            $amountEur = round($amountRub / $eurRate, 2);

            // 4. Возвращаем успешный ответ
            return new JsonResponse([
                'status' => 'created',
                'amount_rub' => $amountRub,
                'rate_eur' => $eurRate,
                'amount_eur' => $amountEur
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'Internal error: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
