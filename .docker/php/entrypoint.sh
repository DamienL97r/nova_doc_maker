#!/bin/sh
set -e

normalize_scripts() {
  # Normalise CRLF -> LF et rend exécutable
  for f in "$@"; do
    [ -f "$f" ] || continue
    sed -i 's/\r$//' "$f" || true
    chmod +x "$f" || true
  done
}

# 0) S'assurer que 'php' existe (utile si seule /usr/bin/php84 est dispo)
command -v php >/dev/null 2>&1 || ln -sf /usr/bin/php84 /usr/local/bin/php

# 1) NORMALISATION AVANT composer (car composer va exécuter bin/console)
normalize_scripts bin/console

# 2) Si premier arg commence par '-', on démarre php-fpm
if [ "${1#-}" != "$1" ]; then
  set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
  composer install --prefer-dist --no-interaction

  # 3) NORMALISATION APRES composer (vendor/bin/* fraichement créés)
  #    On les convertit en LF et on les rend exécutables si besoin.
  if [ -d vendor/bin ]; then
    # shellcheck disable=SC2045
    for f in $(ls -1 vendor/bin 2>/dev/null); do
      normalize_scripts "vendor/bin/$f"
    done
  fi

  # 4) Eventuels scripts binaires Symfony/UX qui utilisent env php
  normalize_scripts bin/console

  # 5) Migrations (optionnel)
  bin/console doctrine:migrations:migrate --no-interaction || true
  # bin/console importmap:install || true  # si tu veux forcer après normalisation
fi

exec "$@"
