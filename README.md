# Установка и настройка проекта Symfony

## Шаг 1: Клонирование репозитория
```bash
git clone <URL вашего репозитория>
cd <название вашего проекта>
```
## Шаг 2: Установка зависимостей
```bash
composer install
```
### Шаг 3: Настройка переменных окружения
Скопируйте файл .env и переименуйте его в .env.local Затем отредактируйте его для настройки подключения к базе данных PostgreSQL.
Пример:

```bash
DATABASE_URL="postgresql://<пользователь>:<пароль>@127.0.0.1:5432/<название_базы_данных>?serverVersion=13&charset=utf8"
```
## Шаг 4: Создание базы данных PostgreSQL
```bash
php bin/console doctrine:database:create --if-not-exists # Создает базу данных, если она не существует
```
## Шаг 5: Миграции
```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```
## Шаг 6: Обновление схемы
```bash
php bin/console doctrine:schema:update --force # Обновляет схему
```
## Шаг 7: Загрузка фикстур
```bash
php bin/console doctrine:fixtures:load
```
## Шаг 8: Запуск сервера
```bash
symfony server:run
```
