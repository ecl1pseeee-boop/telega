# Telega

## 1. Название и описание

**Telega** — мессенджер реального времени с веб-интерфейсом. Пользователь регистрируется (через email/пароль или через GitHub OAuth), добавляет других пользователей в друзья, создаёт личные (`direct`) и групповые (`group`) чаты и обменивается в них сообщениями, которые мгновенно появляются у всех участников чата без перезагрузки страницы. Бизнес-логика и хранение данных реализованы на **Laravel** (PHP, с Inertia.js + React на фронтенде), а доставка сообщений в реальном времени — на отдельном сервисе **FastAPI** (Python), который держит WebSocket-соединения и транслирует события через **Redis Pub/Sub**. Всё окружение поднимается через **Docker Compose** и проксируется через **Nginx**.

## 2. Архитектура

Два независимых backend-сервиса, общие MySQL и Redis, единая точка входа — Nginx.

```
                                 ┌───────────────────────────┐
                                 │           Browser          │
                                 │  React (Inertia.js)        │
                                 │  - HTTP запросы (форма,     │
                                 │    создание чата/сообщения)│
                                 │  - WebSocket (живые события)│
                                 └──────────┬──────────────────┘
                                            │
                                 HTTPS (443) │ ws:// /ws/{chat_id}
                                            ▼
                              ┌──────────────────────────────┐
                              │            Nginx              │
                              │  /          → Laravel (PHP-FPM)│
                              │  /api/      → FastAPI :8000    │
                              │  /ws/       → FastAPI :8000     │
                              │              (proxy upgrade)    │
                              └───────┬───────────────┬────────┘
                                      │               │
                      обычные HTTP-запросы      WebSocket-соединение
                       (auth, CRUD чатов/сообщений) (подписка на chat_id)
                                      │               │
                                      ▼               ▼
                       ┌─────────────────────┐ ┌─────────────────────┐
                       │   Laravel (PHP-FPM)  │ │   FastAPI (Uvicorn)  │
                       │  - Auth (sessions,    │ │  - WebSocket endpoint │
                       │    GitHub OAuth)      │ │    /ws/{chat_id}      │
                       │  - ChatController      │ │  - Слушает Redis      │
                       │  - MessageController   │ │    channel            │
                       │  - UserFriendsController│ │    "chat_channel"    │
                       │  - Создаёт записи в БД │ │  - Ретранслирует      │
                       │  - Публикует событие   │ │    события всем       │
                       │    в Redis после        │ │    подключённым к     │
                       │    создания/изменения/  │ │    конкретному chat_id│
                       │    удаления сообщения   │ │    клиентам            │
                       └──────────┬──────────────┘ └───────────┬───────────┘
                                  │                              │
                                  ▼                              │
                          ┌───────────────┐                       │
                          │     MySQL      │                       │
                          │ users, chats,   │                       │
                          │ messages,       │                       │
                          │ chat_members,   │                       │
                          │ user_friends    │                       │
                          └───────────────┘                       │
                                  ▲                                 │
                                  │      PUBLISH "chat_channel"     │
                                  └─────────────  Redis  ◄───────────┘
                                          (брокер сообщений + кэш Laravel)
```

**Поток отправки сообщения** (ключевой сценарий real-time):

1. Пользователь A открывает `/chat/{id}` → React-компонент `ChatWindow` открывает `WebSocket` на `wss://.../ws/{chat_id}`.
2. Пользователь A отправляет сообщение через обычный HTTP POST (`Inertia`-форма) на Laravel-роут `message.store`.
3. Laravel (`MessageController` → `MessageService`) сохраняет сообщение в MySQL и публикует JSON-событие `{"action": "message.created", "data": {...}}` в Redis-канал `chat_channel`.
4. FastAPI-процесс (`redis_listener`) подписан на `chat_channel`, получает событие и через `ConnectionManager` рассылает его всем WebSocket-клиентам, подключённым к этому `chat_id`.
5. У пользователей A и B, открывших тот же чат, React получает сообщение по WebSocket и добавляет его в список без перезагрузки страницы.

То же самое происходит при редактировании (`message.updated`) и удалении (`message.deleted`) сообщения.

## 3. Запуск

Требуются установленные **Docker** и **Docker Compose**.

```bash
# 1. Клонировать репозиторий
git clone https://github.com/ecl1pseeee-boop/telega.git
cd telega

# 2. Скопировать конфиги окружения
cp .env.example .env
cp telega-laravel/.env.example telega-laravel/.env
```

Отредактируйте `.env` (в корне — для MySQL/FastAPI/Redis) и `telega-laravel/.env` (для Laravel), при необходимости поменяв пароли. Хост базы данных для Laravel — `mysql` (имя сервиса в Docker-сети), для FastAPI задаётся переменной `DATABASE_URL` в корневом `.env`.

```bash
# 3. Собрать и запустить контейнеры
docker-compose up -d --build
```

```bash
# 4. Установить зависимости и подготовить Laravel
docker-compose exec laravel composer install
docker-compose exec laravel php artisan key:generate

# 5. Прогнать миграции и засеять тестовыми данными
docker-compose exec laravel php artisan migrate --seed
```

```bash
# 6. Собрать фронтенд (React + Vite)
docker-compose exec laravel npm install
docker-compose exec laravel npm run build
```
> Если в `/etc/hosts` не прописан домен `telega.local`, браузер выдаст предупреждение о самоподписанном сертификате — это ожидаемо для локального окружения, нужно подтвердить переход.

После выполнения `--seed` в системе уже будет пользователь `admin@admin.com` (пароль генерируется фабрикой, см. `UserFactory`), несколько личных и групповых чатов и тестовая переписка — удобно для быстрой проверки интерфейса.

## 4. Структура БД

### Таблицы

| Таблица | Назначение | Ключевые поля |
|---|---|---|
| **users** | Аккаунты пользователей | `id`, `name`, `email` (unique), `password`, `github_id` (unique, nullable), `github_token` (nullable), `email_verified_at` |
| **chats** | Чаты (личные и групповые) | `id`, `type` (`direct`\|`group`), `title` (nullable), `created_by` → `users.id` |
| **chat_members** | Связь «многие-ко-многим» пользователей и чатов + роль | `id`, `chat_id` → `chats.id`, `user_id` → `users.id`, `role` (`owner`\|`member`); уникальная пара `(chat_id, user_id)` |
| **messages** | Сообщения в чатах | `id`, `chat_id` → `chats.id`, `user_id` → `users.id`, `body` (text), `deleted_at` (soft delete) |
| **user_friends** | Список друзей (направленная связь «пользователь → друг») | `id`, `user_id` → `users.id`, `friend_id` → `users.id` |
| **password_reset_tokens** | Токены восстановления пароля | `email` (PK), `token` |
| **sessions** | Сессии веб-аутентификации Laravel | `id` (PK), `user_id`, `ip_address`, `payload` |
| **personal_access_tokens** | API-токены (Laravel Sanctum/Passport, `morphs('tokenable')`) | `id`, `tokenable_type`, `tokenable_id`, `token` (unique), `abilities` |
| **cache**, **jobs** | Системные таблицы Laravel (кэш через БД, очередь задач) | служебные |

### Связи (ER-схема)

```
users (1) ──────< created (N) chats
  │                              │
  │                              │
  │  (M)                    (M)  │
  └───< chat_members >──────────┘
       (role: owner|member)

users (1) ──────< (N) messages >────── (N) chats
   (author)                        (содержит)

users (1) ──< user_friends >── (1) users
        (user_id)      (friend_id)
        — направленная связь, по одной записи на каждую сторону дружбы
```

- `users 1—N chats` (поле `created_by`): один пользователь может создать много чатов; у каждого чата ровно один создатель.
- `users M—N chats` через `chat_members`: один пользователь состоит в нескольких чатах, в чате несколько участников; pivot-таблица хранит `role`.
- `chats 1—N messages`: при удалении чата каскадно удаляются все его сообщения (`cascadeOnDelete`).
- `users 1—N messages`: один пользователь — автор многих сообщений; при удалении пользователя его сообщения удаляются каскадно.
- `users M—N users` через `user_friends` (self-referencing): запись `(user_id, friend_id)` означает «у user_id есть друг friend_id»; связь не симметрична автоматически — для взаимной дружбы создаются две записи.