.PHONY: dev-up dev-down test-up test-down ps logs clean

# --- DEV ОКРУЖЕНИЕ ---
dev-up:
	docker compose --profile dev up -d

dev-down:
	docker compose --profile dev down

dev-exec:
	docker compose --profile dev exec -u nonroot php bash

# --- TEST ОКРУЖЕНИЕ ---
test-up:
	docker compose --env-file .env --env-file .env.test --profile test up -d

test-down:
	docker compose --env-file .env --env-file .env.test --profile test down

# --- УТИЛИТЫ ДЛЯ СТАТУСА И ЛОГОВ ---
ps:
	docker compose --profile dev --profile test ps

logs:
	docker compose --profile dev --profile test logs -f

# --- ПОЛНАЯ ОЧИСТКА (Сброс кэша и баз данных) ---
clean:
	docker compose --profile dev --profile test down -v

# --- Пересборка акторов для тестов
test-build:
	docker compose --env-file .env --env-file .env.test --profile test run --rm --entrypoint "vendor/bin/codecept build" php-test

# --- Запуск API-тестов в изолированном контейнере
test-run:
	docker compose --env-file .env --env-file .env.test --profile test exec -u nonroot php-test sh -c "\
		php bin/console doctrine:database:create --if-not-exists --env=test && \
		php bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing --env=test && \
		vendor/bin/codecept run Api"
