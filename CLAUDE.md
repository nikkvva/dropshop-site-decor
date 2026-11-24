# dropshop-site — сайт-заготовка (CLAUDE.md)

Часть общего проекта `dropshop` (папка выше, свой репозиторий): там общая картина, план сайтов (`../docs/sites-plan.md`) и правила для всех частей.

## Окружение
- Docker, как в `dropshop-core`: все команды php, artisan, composer — через `docker compose exec app ...` из этой папки.
- `vendor/` живёт в томе Docker, на диске его нет. Код библиотек смотреть внутри контейнера.
- Админка: http://localhost:8081/admin · БД снаружи: localhost:3308.
- `APP_LOCALE=uk`: сайт открытый, всё для клиента — на украинском (переводы Laravel — пакет `laravel-lang`, папка `lang/uk`).

## Что уже есть
- Пустой Laravel 13 + Filament 5, вход в админку со строгим лимитом (`app/Filament/Auth/Login.php`, тест `tests/Feature/AdminLoginTest.php`).
- Класс входа лежит вне `app/Filament/Pages`, потому что эта папка сканируется автоматически.

## Команды
- `php artisan test` — тесты
- `php artisan make:filament-user` — создать пользователя админки
