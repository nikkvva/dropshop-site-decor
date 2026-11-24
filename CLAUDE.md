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
- Товары: `php artisan products:sync` раз в час (контейнер `scheduler`) копирует товары категории `CORE_CATEGORY_ID` из dropshop-core в таблицу `products`. Пропавшие из категории не удаляются, а получают `is_available = false`. Админка: раздел «Товары», только просмотр. Тест: `tests/Feature/SyncProductsTest.php`.
- Связь с dropshop-core: `CORE_URL` (из Docker — `http://host.docker.internal:8080`), `CORE_API_KEY` (ключ из `SITE_API_KEYS` dropshop-core), `CORE_CATEGORY_ID` (демо: 343 «Смартфоны и аксессуары»).

## План шага 4 (сайт-магазин)
- [x] 4.1 Товары из dropshop-core + синк раз в час
- [ ] 4.2 Вёрстка Bazaro → Blade (главная `index-electronics`)
- [ ] 4.3 Каталог и страница товара, SEO
- [ ] 4.4 Корзина, чекаут → заказ у себя + `POST /api/orders` в dropshop-core
- [ ] 4.5 Картинки сайта меняются в админке

## Команды
- `php artisan test` — тесты
- `php artisan make:filament-user` — создать пользователя админки
