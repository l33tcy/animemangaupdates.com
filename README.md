# animemangaupdates.com

Anime & manga news site, WordPress with a custom classic theme (`amu`), deployed
as a Dokploy Compose service behind Traefik.

## Architecture

- **`wordpress`**, `wordpress:6.7-php8.3-apache`, built from this repo (`build: .`).
  The theme is baked into the image and re-synced to the persistent `wp-content`
  volume on every start (`docker-entrypoint-amu.sh`). `wp-config.php` is regenerated
  from environment each start.
- **`db`**, dedicated `mysql:8` with its own `amu_db` volume.
- **Persistence**, `amu_wpcontent` (uploads, plugins) and `amu_db` (database)
  survive deploys. The **theme is git-managed**: push here, redeploy, it updates.

## Deploy (Dokploy)

1. New **Compose** service → Git source → this repo → branch `main`.
2. Environment tab: set the vars from `.env.example` (real `DB_PASSWORD` etc.).
3. Domains: `animemangaupdates.com` + `www` → port 80, HTTPS (Let's Encrypt).
4. Deploy. DNS `A animemangaupdates.com → 91.99.232.31` (and `www`) must resolve
   here for TLS to issue.

## Theme development

Edit `wp-content/themes/amu/`, commit, push, redeploy. The stylesheet is a single
file (`style.css`); templates are plain classic PHP. Uploads/plugins set in wp-admin
persist across deploys.
