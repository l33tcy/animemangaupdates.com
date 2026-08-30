# Persistence model:
#   THEME   -> git-managed: baked into the image, re-synced to the volume on every
#              start (source of truth is this repo; auto-updates on deploy).
#   PLUGINS -> persisted: ACF + Yoast are baked in as a seed and copied into the
#              wp-content volume ONCE (first run); afterwards they live in the
#              volume and update via wp-admin — deploys never overwrite them.
#   DATA    -> persisted: uploads in the wp-content volume, everything else in the
#              separate MySQL service.
FROM wordpress:6.7-php8.3-apache

# Build/runtime tools + wp-cli (used by the entrypoint's one-time setup).
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends git unzip curl less; \
    rm -rf /var/lib/apt/lists/*; \
    curl -fsSL https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -o /usr/local/bin/wp; \
    chmod +x /usr/local/bin/wp

# Persisted plugins (real plugins, no bridges): Advanced Custom Fields + Yoast SEO.
RUN set -eux; mkdir -p /opt/amu-seed/plugins; \
    curl -fsSL "https://downloads.wordpress.org/plugin/advanced-custom-fields.latest-stable.zip" -o /tmp/acf.zip; \
    unzip -oq /tmp/acf.zip -d /opt/amu-seed/plugins; rm -f /tmp/acf.zip; \
    curl -fsSL "https://downloads.wordpress.org/plugin/wordpress-seo.latest-stable.zip" -o /tmp/yoast.zip; \
    unzip -oq /tmp/yoast.zip -d /opt/amu-seed/plugins; rm -f /tmp/yoast.zip

# Git-deployed: the theme (refreshed from the image on every start).
COPY wp-content/themes/amu/ /opt/amu-theme/amu/

RUN printf 'upload_max_filesize = 64M\npost_max_size = 64M\nmemory_limit = 256M\nmax_execution_time = 120\n' \
    > /usr/local/etc/php/conf.d/amu-limits.ini

HEALTHCHECK --interval=10s --timeout=5s --start-period=50s --retries=3 \
  CMD curl -fso /dev/null http://127.0.0.1/wp-login.php || exit 1

COPY docker-entrypoint-amu.sh /usr/local/bin/docker-entrypoint-amu.sh
RUN chmod +x /usr/local/bin/docker-entrypoint-amu.sh
ENTRYPOINT ["docker-entrypoint-amu.sh"]
CMD ["apache2-foreground"]
