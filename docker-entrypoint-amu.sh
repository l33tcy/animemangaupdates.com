#!/bin/bash
# Persistence model (see Dockerfile): theme = git-managed (refreshed every start),
# plugins + uploads = persisted in the wp-content volume, data = MySQL service.
set -euo pipefail

WPC=/var/www/html/wp-content
mkdir -p "$WPC/themes/amu" "$WPC/plugins"

# CORE — the wordpress image declares VOLUME /var/www/html, so Docker keeps an
# anonymous volume that pins an OLD core across deploys (the stock entrypoint
# only copies missing files, never upgrades). Force the docroot core to match the
# image on every start, excluding the wp-content data volume.
# Only overwrite when the image ships a NEWER core than the docroot, so WordPress
# auto-updates that landed in the volume are preserved across deploys.
if [ -d /usr/src/wordpress ]; then
  imgver=$(grep -oE "wp_version = '[^']+'" /usr/src/wordpress/wp-includes/version.php | grep -oE '[0-9.]+' | head -1)
  curver=$(grep -oE "wp_version = '[^']+'" /var/www/html/wp-includes/version.php 2>/dev/null | grep -oE '[0-9.]+' | head -1)
  if [ -z "$curver" ] || [ "$(printf '%s\n%s\n' "$curver" "$imgver" | sort -V | tail -1)" != "$curver" ]; then
    ( cd /usr/src/wordpress && tar --exclude='./wp-content' -cf - . ) | ( cd /var/www/html && tar -xf - )
    echo "[core] synced ${curver:-none} -> $imgver from image"
  else
    echo "[core] keeping docroot core $curver (image ships $imgver)"
  fi
fi

# THEME — always refreshed from the image (git is the source of truth).
cp -a /opt/amu-theme/amu/. "$WPC/themes/amu/"

# PLUGINS — seed ACF + Yoast ONCE (only if missing), then leave them to the site
# so wp-admin updates persist across deploys.
for p in advanced-custom-fields wordpress-seo; do
  [ -e "$WPC/plugins/$p" ] || cp -a "/opt/amu-seed/plugins/$p" "$WPC/plugins/" 2>/dev/null || true
done

chown -R www-data:www-data "$WPC" 2>/dev/null || true

# Config stays driven by the Dokploy env — regenerate wp-config from env each start.
rm -f /var/www/html/wp-config.php 2>/dev/null || true

# One-time / idempotent site setup, in the background once WP + DB are reachable.
(
  MARK="$WPC/.amu-setup"
  wpc() { wp --allow-root --path=/var/www/html "$@"; }
  for _ in $(seq 1 60); do wpc core is-installed >/dev/null 2>&1 && break; sleep 5; done
  wpc core is-installed >/dev/null 2>&1 || exit 0

  # Apply any DB schema upgrade after a core version bump (idempotent).
  wpc core update-db >/dev/null 2>&1 || true

  # Keep the persisted plugins + git theme active (idempotent every deploy).
  wpc plugin is-active advanced-custom-fields >/dev/null 2>&1 || wpc plugin activate advanced-custom-fields >/dev/null 2>&1 || true
  wpc plugin is-active wordpress-seo          >/dev/null 2>&1 || wpc plugin activate wordpress-seo          >/dev/null 2>&1 || true
  wpc theme activate amu >/dev/null 2>&1 || true

  # First-run only: pretty permalinks + Yoast schema identity (Organization).
  if [ ! -f "$MARK" ]; then
    wpc rewrite structure '/%postname%/' --hard >/dev/null 2>&1 || true
    NAME="$(wpc option get blogname 2>/dev/null || echo AnimeMangaUpdates)"
    wpc option patch update wpseo_titles company_or_person company >/dev/null 2>&1 || true
    wpc option patch update wpseo_titles company_name "$NAME"      >/dev/null 2>&1 || true
    wpc option patch update wpseo social_pages_disabled 0          >/dev/null 2>&1 || true
    touch "$MARK"
  fi
) &

exec docker-entrypoint.sh "$@"
