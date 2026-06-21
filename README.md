# Telega Project

Система обмена сообщениями в реальном времени, построенная на стеке **Laravel** (Backend API/Inertia) и **FastAPI** (WebSocket/Real-time сервис), работающая в Docker.

## Быстрый старт

Убедитесь, что у вас установлены [Docker](https://www.docker.com/) и [Docker Compose](https://docs.docker.com/).

### 1. Подготовка окружения
Скопируйте примеры файлов конфигурации:

```bash
# В корне проекта
cp .env.example .env

# Для Laravel
cp telega-laravel/.env.example telega-laravel/.env
```

Отредактируйте созданные .env файлы, указав там корректные параметры подключения к базе данных (хост базы данных для Laravel — mysql).

### 2. Сборка и запуск
Для первого запуска соберите образы и поднимите контейнеры:

```bash
docker-compose up -d --build
```

### 3. Настройка Laravel
Выполните необходимые команды внутри контейнера Laravel:

```bash
# Установка зависимостей Composer
docker-compose exec laravel composer install

# Генерация ключа приложения
docker-compose exec laravel php artisan key:generate

# Запуск миграций базы данных
docker-compose exec laravel php artisan migrate --seed
```

### 4. Сборка фронтенда
Для работы интерфейса (React + Vite) необходимо скомпилировать ассеты:

```bash
# Установка NPM зависимостей
docker-compose exec laravel npm install

# Сборка ассетов
docker-compose exec laravel npm run build
```

## Архитектура системы
Nginx: Прокси-сервер, распределяющий запросы:
    /api -> FastAPI
    /ws -> WebSocket (FastAPI)
    / -> Laravel

Laravel: Основной бэкенд, управление пользователями, бизнес-логика.
FastAPI: Высокопроизводительный сервис для обработки WebSocket-соединений.
MySQL: База данных.
Redis: Брокер сообщений и кэш.