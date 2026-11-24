# Dropshop — сайт-заготовка

Заготовка, из которой делаются сайты-магазины под отдельные ниши. Товары и наличие берутся из каталога `dropshop-core`, заказы уходят туда же.

## Из чего состоит

- **Docker**: `nginx` (веб-сервер), `app` (PHP 8.3-FPM), `db` (MySQL 8.0), `scheduler` (задачи по расписанию).
- **Laravel 13** + админка **Filament 5** по адресу `/admin`. Язык — украинский.
- Вход в админку только по email и паролю, регистрации нет.
- Защита от перебора паролей: после 5 попыток входа с одного IP вход закрывается на 15 минут (`app/Filament/Auth/Login.php`).

## Запуск с нуля

Нужны Git и Docker Desktop (на Linux — Docker с Compose). Команды одинаковые для PowerShell и bash.

```
git clone https://github.com/nikkvva/dropshop-site.git
cd dropshop-site
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan make:filament-user
```

После этого открыть **http://localhost:8081/admin**.

Порты отличаются от `dropshop-core` (8080 / 3307), чтобы оба проекта работали одновременно: сайт — **8081**, база снаружи — **3308**.

## Тесты

```
docker compose exec app php artisan test
```
