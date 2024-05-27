# Установка и настройка проекта Symfony

## Шаг 1: Клонирование репозитория
```bash
git clone https://github.com/kylym1631/vehicle-registration-system.git
cd vehicle-registration-system
```
## Шаг 2: Установка зависимостей
```bash
composer install
```
### Шаг 3: Настройка переменных окружения
Создайте файл .env.local Затем наишите команду для настройки подключения к базе данных PostgreSQL.

```bash
DATABASE_URL="postgresql://<пользователь>:<пароль>@127.0.0.1:5432/<название_базы_данных>?serverVersion=15&charset=utf8"
```
## Шаг 4: Создание базы данных PostgreSQL
```bash
php bin/console doctrine:database:create --if-not-exists -n # Создает базу данных, если она не существует
```
### Всегда отвечайте 'yes' когда промпт спросит о чем-то

## Шаг 5: Миграции
```bash
php bin/console make:migration -n
php bin/console doctrine:migrations:migrate -n
```
## Шаг 6: Обновление схемы
```bash
php bin/console doctrine:schema:update --force -n # Обновляет схему
```
## Шаг 7: Загрузка фикстур
```bash
php bin/console doctrine:fixtures:load -n
```
## Шаг 8: Запуск сервера
```bash
symfony server:start --no-tls
```
## Скриншоты
![Example Image](https://github.com/kylym1631/vehicle-registration-system/blob/main/docs/images/list.jpeg)
![Example Image](https://github.com/kylym1631/vehicle-registration-system/blob/main/docs/images/history.jpeg)
# Как бы я улучшил
    * Поиск и выбор по номерам, владельцам, марке и модели
    * Экспорт повторяющихся компонентов (кнопок и т.д.)
    * Мобильный адаптивный
    * Я бы добавил настраиваемые иконки на кнопки действий
    * Фильтры, пагинация