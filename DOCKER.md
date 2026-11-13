# ADMIX Docker Guide

This repository has separate Docker Compose configurations for local development and production. Never commit `.env`, `.env.production`, database dumps, or real credentials.

## Local development (Windows / Docker Desktop)

1. Start Docker Desktop and open PowerShell in the repository root.
2. If `.env` does not exist, create it from the example:

   `Copy-Item .env.example .env`

3. Make sure `APP_KEY` in `.env` is populated. If it is empty, run `docker compose run --rm app php artisan key:generate` after the first image build, or generate it with your local PHP installation.
4. Start the stack:

   `docker compose up -d --build`

5. Open `http://localhost:8080`. Vite is available at port `5173`.
6. Check service status and logs:

   `docker compose ps`

   `docker compose logs -f app web db node`

The development database is MariaDB 10.11, exposed to the Windows host at `127.0.0.1:3307`. The default development database is `admix`, user `admix`, password `admix_local_password`. These are local-only defaults. Override them with `ADMIX_DB_NAME`, `ADMIX_DB_USER`, `ADMIX_DB_PASSWORD`, and `ADMIX_DB_ROOT_PASSWORD` in the root `.env` file.

To run Artisan commands:

`docker compose exec app php artisan about`

`docker compose exec app php artisan migrate`

Only run migrations on a new/empty Docker database. For the existing XAMPP database, export a backup from phpMyAdmin and import it into Docker as a separate migration step before changing or deleting the XAMPP database.

To create the public storage link in development:

`docker compose exec app php artisan storage:link`

Stop the services without deleting database data:

`docker compose down`

Do not use `docker compose down -v` unless you intentionally want to delete the Docker database and Node dependency volumes.

## Production

1. Copy `.env.production.example` to `.env.production` and set a real domain, a generated Laravel `APP_KEY`, and unique strong database passwords.
2. Keep `.env.production` private; it is ignored by Git.
3. Build and start production:

   `docker compose --env-file .env.production -f compose.production.yaml up -d --build`

4. After the production database has been initialized or the existing database has been imported, run:

   `docker compose --env-file .env.production -f compose.production.yaml exec app php artisan migrate --force`

The production Compose file includes PHP-FPM, Nginx, MariaDB, and a database queue worker. It does not publish the database port. Configure HTTPS at the host/reverse-proxy layer before exposing the application publicly.

## Important notes

- Development uses host port `8080` for the app, `5173` for Vite, and `3307` for MariaDB so it does not conflict with the usual XAMPP MySQL/MariaDB port `3306`.
- Development and production use separate Compose project names and separate named volumes.
- The existing XAMPP database is not modified by these files. Back it up before migration.
- The current repository has no `package-lock.json`; the frontend image currently installs dependencies from `package.json`. Adding and committing a lockfile is recommended for fully repeatable frontend builds.
- MariaDB 10.11 is selected as a newer LTS target for the existing MariaDB 10.4.32 database. Verify the exported SQL backup on the Docker database before switching the application over.
