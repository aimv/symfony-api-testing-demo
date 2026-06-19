<?php

namespace App\Tests\Api\v1;

use App\Tests\Support\ApiTester;
use Codeception\Util\HttpCode;

class CreateOrderCest
{
    // Тест проверяет успешный сценарий, когда WireMock отдает курс 100.0
    public function testCreateOrderSuccess(ApiTester $I): void
    {
        // Настраиваем заголовки запроса API
        $I->haveHttpHeader('Content-Type', 'application/json');

        // Отправляем POST запрос на создание заказа
        $I->sendPost('orders', [
            'amount_rub' => 5000
        ]);

        // Проверяем HTTP статус ответа (201 Created)
        $I->seeResponseCodeIs(HttpCode::CREATED);

        // Проверяем валидность JSON структуры и точные значения
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'status' => 'created',
            'amount_rub' => 5000.0,
            'rate_eur' => 100.0,   // Значение берется из маппинга WireMock
            'amount_eur' => 50.0   // 5000 / 100 = 50
        ]);
    }

    // Тест проверяет валидацию некорректных входных данных
    public function testCreateOrderValidationFailed(ApiTester $I): void
    {
        $I->haveHttpHeader('Content-Type', 'application/json');

        // Отправляем некорректное значение суммы
        $I->sendPost('orders', [
            'amount_rub' => 'not-a-number'
        ]);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson([
            'error' => 'Invalid or missing amount_rub'
        ]);
    }
}
