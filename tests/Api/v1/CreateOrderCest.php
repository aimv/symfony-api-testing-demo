<?php

namespace App\Tests\Api\v1;

use App\Entity\Company;
use App\Tests\Support\ApiTester;
use Codeception\Util\HttpCode;
use Symfony\Component\Uid\Ulid;

class CreateOrderCest
{
    // Этот метод выполняется автоматически ПЕРЕД каждым тестом в этом классе
    public function _before(ApiTester $I): void
    {
        // Перед стартом теста принудительно загружаем наши связанные фикстуры.
        // Загрузчик автоматически поймет зависимости: сначала создаст User, потом Company.
        $I->loadFixtures(\App\DataFixtures\CompanyFixtures::class);
    }

    // Тест успешного создания инвойса (заказа)
    public function testCreateOrderSuccess(ApiTester $I): void
    {
        // 1. Извлекаем созданную фикстурой компанию прямо из тестовой БД через репозиторий
        /** @var Company $company */
        $company = $I->grabEntityFromRepository(Company::class, [
            'name' => 'Middle Tech LLC'
        ]);

        $I->haveHttpHeader('Content-Type', 'application/json');

        // 2. Отправляем POST запрос, передавая реальный ULID компании
        $I->sendPost('orders', [
            'company_id' => $company->id->toString(), // Используем хук свойства id!
            'amount_rub' => 5000
        ]);

        // 3. Проверяем HTTP статус ответа (201 Created)
        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeResponseIsJson();

        // 4. Проверяем структуру JSON-ответа и точные расчеты валют
        $I->seeResponseContainsJson([
            'status' => 'created',
            'company_name' => 'Middle Tech LLC',
            'amount_rub' => 5000.0,
            'rate_eur' => 100.0,   // Значение берется из маппинга WireMock
            'amount_eur' => 50.0   // 5000 / 100 = 50.00
        ]);

        // 5. Проверяем, что ID созданного заказа вернулся в формате ULID/UUID
        $I->seeResponseMatchesJsonType([
            'order_id' => 'string:regex(/^([0-9a-fA-F]){8}-([0-9a-fA-F]){4}-([0-9a-fA-F]){4}-([0-9a-fA-F]){4}-([0-9a-fA-F]){12}$/)',
        ]);
    }

    // Тест ошибки, если компания не найдена в базе данных
    public function testCreateOrderCompanyNotFound(ApiTester $I): void
    {
        $I->haveHttpHeader('Content-Type', 'application/json');

        // Генерируем случайный, валидный по структуре, но несуществующий ULID
        $fakeCompanyId = new Ulid()->toString();

        $I->sendPost('orders', [
            'company_id' => $fakeCompanyId,
            'amount_rub' => 5000
        ]);

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeResponseContainsJson([
            'error' => 'Company not found'
        ]);
    }

    // Тест валидации некорректных входных данных
    public function testCreateOrderValidationFailed(ApiTester $I): void
    {
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPost('orders', [
            'company_id' => 'not-a-valid-ulid',
            'amount_rub' => -100
        ]);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }
}
