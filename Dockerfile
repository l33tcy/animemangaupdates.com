# Persistence model: wp-content is a PERSISTENT VOLUME in prod (uploads, plugins,
# languages survive deploys). Only the THEME is git-managed — the entrypoint
# refreshes it from the image on every start. DB is a separate MySQL service.
FROM wordpress:6.7-php8.3-apache

# Git-deployed code, synced from the image on every start: the theme only.
COPY wp-content/themes/amu/ /opt/amu-theme/amu/

# Modest upload limits for a news site (cover art, screenshots).
RUN printf 'upload_max_filesize = 64M\npost_max_size = 64M\nmemory_limit = 256M\nmax_execution_time = 120\n' \
    > /usr/local/etc/php/conf.d/amu-limits.ini

# Healthcheck proves PHP+DB are up; start-period covers the entrypoint's copy work.
HEALTHCHECK --interval=10s --timeout=5s --start-period=40s --retries=3 \
  CMD curl -fso /dev/null http://127.0.0.1/wp-login.php || exit 1

COPY docker-entrypoint-amu.sh /usr/local/bin/docker-entrypoint-amu.sh
RUN chmod +x /usr/local/bin/docker-entrypoint-amu.sh
ENTRYPOINT ["docker-entrypoint-amu.sh"]
CMD ["apache2-foreground"]
