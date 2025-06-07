# Whois Laravel Project

Сервіс для перевірки WHOIS-інформації по домену. Реалізовано на Laravel, фронтенд на Blade.

Додатково потрібно встановити (якщо ще не встановлений) docker та docker-compose.

https://docs.docker.com/engine/

https://docs.docker.com/compose/

## Запуск проекту у Docker

1. Клонуйте репозиторій:
   ```bash
   git clone https://github.com/Michael-pdlsn/test_for_nda_hosting_company.git
   ```
2. Скопиюуйте файли `project/.env.example` у `project/.env` та  `docker/.env.example` у `docker/.env`.
    
   ```bash
    cp test_for_nda_hosting_company/project/.env.example test_for_nda_hosting_company/project/.env
   ```
   ```bash
    cp test_for_nda_hosting_company/docker/.env.example test_for_nda_hosting_company/docker/.env
   ```

4. Зберіть і запустіть контейнери:
   
   ```bash
   cd test_for_nda_hosting_company/docker
   ```

   ```bash
   docker-compose up --build
   ```
4. Відкрийте у браузері:
   
   - http://localhost:8080 (або порт, вказаний у docker/.env  - параметр NGINX_PORT)

## Примітки
- Статичні файли (CSS/JS) мають бути зібрані у папку `public` через Vite. Тому для більш простого запуску скопіював їх у `public` та не став додавати npm у Dockerfile.
- У файлі [docker-entrypoint.sh](docker/php/docker-entrypoint.sh)  встановлюються залежності composer та генерується APP_KEY якщо його ще не існує.
- Використвується PHP 8.3, Nginx остання версія.
- Для роботи з whois використовується локальна команда `whois`, яка встановлена у контейнері.
- Уся логіка додатку знаходиться у сервісі `App\Services\WhoisService`, який викликається з контролера `App\Http\Controllers\WhoisController`.