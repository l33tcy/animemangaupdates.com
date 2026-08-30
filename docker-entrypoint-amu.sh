#!/bin/bash
# Persistence model: wp-content is a persistent volume — uploads, plugins and
# languages survive deploys. ONLY the theme is git-managed: refreshed from the
# image on every start so a deploy always ships the current theme code.
set -euo pipefail

WPC=/var/www/html/wp-content
mkdir -p "$WPC/themes/amu"

# Git-deployed = always refreshed from the image.
cp -a /opt/amu-theme/amu/. "$WPC/themes/amu/"
chown -R www-data:www-data "$WPC" 2>/dev/null || true

# Config stays driven by Dokploy env — drop wp-config.php so the stock entrypoint
# regenerates it from the current environment on every start.
rm -f /var/www/html/wp-config.php 2>/dev/null || true

# Hand off to the stock WordPress entrypoint (runs core install + apache).
exec docker-entrypoint.sh "$@"
