# Whois Laravel Project

A small service that returns WHOIS information for a domain. Built with Laravel 12, the frontend is a single Blade page.

## Requirements

- [Docker](https://docs.docker.com/engine/)
- [Docker Compose](https://docs.docker.com/compose/)

## Running with Docker

1. Clone the repository:
   ```bash
   git clone https://github.com/Michael-pdlsn/test_task_docker_laravel.git
   cd test_task_docker_laravel
   ```
2. Create the environment files from the examples:
   ```bash
   cp project/.env.example project/.env
   cp docker/.env.example docker/.env
   ```
3. Build and start the containers:
   ```bash
   cd docker
   docker compose up --build
   ```
4. Open http://localhost:8080 in a browser (or the port set by `NGINX_PORT` in `docker/.env`).

## Running the tests

```bash
cd docker
docker compose exec app_php php artisan test
```

The tests do not call real WHOIS servers: the `whois` command is replaced with a fake (`Process::fake()`).

## How it works

- `POST /whois` accepts `{"domain": "example.com"}` and returns `{"data": "<whois output>"}` or `{"error": "<message>"}`.
- All logic lives in `App\Services\WhoisService`, called from `App\Http\Controllers\WhoisController`.
- The domain is normalized before the lookup: lower case, trailing dot removed, internationalized names (e.g. `приклад.укр`) converted to punycode (non-transitional IDNA2008, so `ß` is kept). Invalid input is rejected with `422` before any command runs.
- The system `whois` command runs through Laravel's `Process` with the arguments passed directly (no shell) and a 10-second timeout. If it fails or returns nothing, the API answers `502` with a generic message; the details go to the log.

| Status | When |
|---|---|
| 200 | WHOIS answer received: `whois` exit code 0 (found) or 1 ("no match") |
| 422 | Domain missing or invalid |
| 502 | `whois` exit code 2+ (DNS/network error), timeout, failed to start, or empty output |

## Notes

- Static assets (CSS/JS) are committed to `public` so the project runs without Node.js/Vite in the container.
- `docker/php/docker-entrypoint.sh` installs Composer dependencies and generates `APP_KEY` if it is missing.
- PHP 8.3 (FPM) with the `intl` extension, Nginx 1.27.
