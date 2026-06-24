<?php

namespace App\Tests\Api\v1;

use App\DataFixtures\CompanyFixtures;
use App\Entity\Company;
use App\Tests\Support\ApiTester;
use Codeception\Util\HttpCode;
use Symfony\Component\Uid\Uuid;

class GetCompanyOrdersCest
{
    private Company $company;
    private Company $company2;

    public function _before(ApiTester $I): void
    {
        // Загружаем фикстуру компании перед каждым тестом
        $I->loadFixtures(CompanyFixtures::class);

        /** @var Company $company */
        $company = $I->grabEntityFromRepository(Company::class, [
            'name' => 'Middle Tech LLC'
        ]);
        $this->company = $company;

        /** @var Company $company2 */
        $company2 = $I->grabEntityFromRepository(Company::class, [
            'name' => 'Test New Company'
        ]);
        $this->company2 = $company2;
    }

    // Тест 1: Успешное получение списка заказов
    public function testGetOrdersSuccess(ApiTester $I): void
    {
        $I->haveHttpHeader('Content-Type', 'application/json');

        // 1. СНАЧАЛА СОЗДАЕМ ЗАКАЗ ЧЕРЕЗ АПИ (Тест сам наполняет базу!)
        $I->sendPost('orders', [
            'company_id' => $this->company->id->toString(),
            'amount_rub' => 10000.00
        ]);
        $I->seeResponseCodeIs(HttpCode::CREATED);

        // Извлекаем order_id из ответа POST-запроса, чтобы проверить его в списке
        $responseData = json_decode($I->grabResponse(), true);
        $createdOrderId = $responseData['order_id'];

        // 2. ТЕПЕРЬ ЗАПРАШИВАЕМ СПИСОК ЗАКАЗОВ
        $I->sendGet(sprintf('companies/%s/orders', $this->company->id->toRfc4122()));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();

        // 3. ПРОВЕРЯЕМ, ЧТО НАШ СОЗДАННЫЙ ЗАКАЗ ЕСТЬ В СПИСКЕ
        $I->seeResponseContainsJson([
            [
                'order_id' => $createdOrderId, // Проверяем точный ID, сгенерированный системой
                'amount_rub' => 10000.0,
                'rate_eur' => 100.0,
                'amount_eur' => 100.0,
                'company_name' => 'Middle Tech LLC',
                'owner_email' => 'admin@example.com'
            ]
        ]);
    }

    // Тест 2: Заказов у компании нет — должен вернуться пустой массив
    public function testGetOrdersEmptyList(ApiTester $I): void
    {
        $I->sendGet(sprintf('companies/%s/orders', $this->company2->id->toRfc4122()));

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();

        // Проверяем, что тело ответа — это в точности пустой JSON-массив
        $I->seeResponseEquals('[]');
    }

    // Тест 3: Компании не существует
    public function testGetOrdersCompanyNotFound(ApiTester $I): void
    {
        $fakeCompanyId = Uuid::v4()->toRfc4122();

        $I->sendGet(sprintf('companies/%s/orders', $fakeCompanyId));

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeResponseContainsJson([
            'error' => 'Company not found'
        ]);
    }
}

