# syntax=docker/dockerfile:1.4

# (Optionnel) stage postgres - pas utilisé par l'image app-php, conservé pour compat
FROM postgres as database_upstream

# =========================
# PHP-FPM (app_dev)
# =========================
FROM mztrix/php-fpm as app_dev

WORKDIR /var/www/app

# Paquets PHP & outils
RUN set -eux; \
    apk add --no-cache \
    php84 \
    php84-cli \
    php84-phar \
    php84-mbstring \
    php84-iconv \
    php84-openssl \
    php84-ctype \
    php84-sodium \
    php84-xml \
    php84-tokenizer \
    php84-dom \
    php84-simplexml \
    php84-xmlwriter \
    php84-intl \
    php84-session \
    php84-pdo \
    php84-pdo_pgsql \
    php84-pecl-xdebug \
    acl \
    file \
    gettext \
    git \
    curl \
    ca-certificates; \
    update-ca-certificates; \
    # Si "php" n'est pas dans le PATH, crée un alias vers php84 (pour #!/usr/bin/env php)
    command -v php >/dev/null 2>&1 || ln -s /usr/bin/php84 /usr/local/bin/php

# ---- Tailwind CSS (binaire Alpine/musl) ----
# IMPORTANT: on télécharge *tailwindcss-linux-x64-musl* (et pas linux-x64)
RUN set -eux; \
    TAILWIND=/usr/local/bin/tailwindcss; \
    curl -fsSL \
    https://github.com/tailwindlabs/tailwindcss/releases/latest/download/tailwindcss-linux-x64-musl \
    -o "$TAILWIND"; \
    chmod +x "$TAILWIND"; \
    "$TAILWIND" -v

# ---- Sessions PHP: dossier + droits + conf ----
RUN set -eux; \
    mkdir -p /var/lib/php/sessions; \
    chown -R www-data:www-data /var/lib/php; \
    chmod 1733 /var/lib/php/sessions; \
    printf "session.save_handler = files\nsession.save_path = \"/var/lib/php/sessions\"\n" \
    > /etc/php84/conf.d/99-sessions.ini

# ---- Socket PHP-FPM: dossier (partagé avec Nginx via volume) ----
RUN set -eux; \
    mkdir -p /var/run/php; \
    chown -R www-data:www-data /var/run/php

# Confiance git dans /var/www/app
RUN git config --global --add safe.directory /var/www/app

# Tes overrides PHP .ini
COPY --link .docker/php/conf.d/app.ini        /etc/php84/php.ini
COPY --link .docker/php/conf.d/50_xdebug.ini  /etc/php84/conf.d/50_xdebug.ini

# Composer depuis l'image officielle
COPY --from=composer/composer:2-bin --link /composer /usr/local/bin/composer

# Composer files pour le cache des layers
COPY --chown=www-data:www-data composer.json composer.lock ./

# Vendor en volume (comme tu fais déjà via docker compose)
VOLUME /var/www/app/vendor

# ---- Entrypoint custom ----
COPY --link .docker/php/entrypoint.sh /usr/local/bin/entrypoint
RUN set -eux; \
    chmod +x /usr/local/bin/entrypoint; \
    sed -i 's/\r$//' /usr/local/bin/entrypoint

# (Optionnel) nettoyage au build — n'agit pas si tu bind-mount le code ensuite
RUN set -eux; \
    sed -i 's/\r$//' bin/console 2>/dev/null || true; \
    chmod +x bin/console 2>/dev/null || true

ENTRYPOINT ["/usr/local/bin/entrypoint"]


# =========================
# (Optionnel) Stage DB
# =========================
FROM database_upstream as database_dev
# (rien ici – tu utilises l'image postgres via docker compose)

# =========================
# Nginx
# =========================
FROM nginx:alpine AS nginx_dev

WORKDIR /var/www/app/public

RUN set -eux; \
    adduser -D -u 82 -S -G www-data -s /sbin/nologin www-data; \
    chown -R www-data:www-data /var/www/app

# Config Nginx
COPY --link .docker/nginx/sites-enabled/api.conf /etc/nginx/conf.d/default.conf
COPY --link .docker/nginx/nginx.conf             /etc/nginx/nginx.conf

CMD ["nginx", "-g", "daemon off;"]
