#!/bin/sh
set -e

# First arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
	# Attendre que la base de données soit prête
	echo "Attente de la base de données..."
	until nc -z database 5432; do
	  echo "La base de données n'est pas encore prête... en attente de 1s"
	  sleep 1
	done
	echo "Base de données prête !"

	# Exécution des migrations
	echo "Exécution des migrations..."
	php bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing

	# Chargement des fixtures (si spécifié par LOAD_FIXTURES=true)
	if [ "$LOAD_FIXTURES" = "true" ]; then
		echo "Chargement des fixtures..."
		php bin/console doctrine:fixtures:load --no-interaction
	fi
fi

exec docker-php-entrypoint "$@"
