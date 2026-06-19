#!/bin/sh
set -e

if [ "$1" = 'frankenphp' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then

	if [ -z "$(ls -A 'vendor/' 2>/dev/null)" ]; then
		composer install --prefer-dist --no-progress --no-interaction
	fi

	# Вывод версии Symfony для логов
	php bin/console -V

	# Накатываем миграции структуры таблиц, если они есть
	# База данных гарантированно будет существовать, так как Docker Compose
	# не запустит PHP, пока Postgres не отчитается о её создании через healthcheck
	if [ "$(find ./migrations -iname '*.php' -print -quit)" ]; then
		echo "Running database migrations..."
		php bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing
	fi

	echo 'PHP app ready!'
fi

exec docker-php-entrypoint "$@"
