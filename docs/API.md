# API Documentation

Base URL: `/api`

Все эндпоинты возвращают JSON. Формат успешного ответа:

```json
{
  "result": <data>,
  "timestamp": 1714912345
}
```

Формат ошибки:

```json
{
  "error": "ERROR_CODE",
  "errorMessage": "Описание ошибки"
}
```

---

## Аутентификация

### POST `/api/auth/register`

Регистрация нового пользователя.

**Request Body:**

| Поле | Тип | Обязательное | Ограничения |
|------|-----|-------------|-------------|
| email | string | Да | Email, max 255 |
| password | string | Да | min 8 |
| login | string \| null | Нет | max 255 |
| name | string \| null | Нет | max 255 |
| lastName | string \| null | Нет | max 255 |
| middleName | string \| null | Нет | max 255 |

**Response:** `UserResponse` (201)

---

### POST `/api/auth/token/refresh` (LexikJWTBundle)

Обновление JWT токена.

В ответ приходит установка токенов в куки

## Пользователи

Все эндпоинты требуют JWT авторизацию (кроме `/api/auth/register` и `/api/auth/token`).

### GET `/api/users/profile`

Получение профиля текущего пользователя.

**Response:** `UserResponse`

---

### PUT|PATCH `/api/users/profile`

Обновление профиля текущего пользователя.

**Request Body:**

| Поле | Тип | Обязательное | Ограничения |
|------|-----|-------------|-------------|
| name | string \| null | Нет | min 2, max 255 |
| lastName | string \| null | Нет | min 2, max 255 |
| middleName | string \| null | Нет | min 2, max 255 |
| email | string \| null | Нет | Email, max 255 |
| login | string \| null | Нет | min 3, max 50, /^[a-zA-Z0-9_]+$/ |
| avatar | string \| null | Нет | base64 image (jpeg/png/webp) |

**Response:** `UserResponse`

---

### GET `/api/users/{id}`

Получение пользователя по ID.

**Path Parameters:** `id` — int

**Response:** `UserResponse`

---

### GET `/api/users/profile/settings`

Получение настроек текущего пользователя.

**Response:** `UserSettingsResponse`

---

### PUT|PATCH `/api/users/profile/settings`

Обновление настроек текущего пользователя.

**Request Body:**

| Поле | Тип | Обязательное | Ограничения |
|------|-----|-------------|-------------|
| generationModel | string | Да | Один из: `qwen/qwen3.6-27b`, `qwen/qwen3.5-27b`, `qwen/qwen3.6-plus` |
| generationCreative | float | Да | min 0, max 1 |

**Response:** `UserSettingsResponse`

---

## Игры

### POST `/api/games/generate`

Генерация игры через AI. Требует JWT.

**Request Body:**

| Поле | Тип | Обязательное | Ограничения |
|------|-----|-------------|-------------|
| age | int | Да | min 3, max 80 |
| players | int | Да | min 1, max 500 |
| duration | int | Да | min 5, max 480 |
| locationType | string | Да | `indoor`, `outdoor`, `both` |
| fieldWidth | int | Да | min 1, max 1000 |
| fieldLength | int | Да | min 1, max 1000 |
| activityLevel | string | Да | `low`, `medium`, `high` |
| requisites | string[] \| null | Нет | каждый: string, not blank, max 100 |
| photos | string[] | Нет | каждый: base64 image (jpeg/png/webp/gif) |
| locationDescription | string \| null | Условно обязательное | min 10. **Обязательно, если `photos` пустой** |

**Response:** `GameResponse` (201)

---

### GET `/api/games`

Список публичных игр.

**Query Parameters:**

| Параметр | Тип | По умолчанию |
|----------|-----|-------------|
| page | int | 1 |
| limit | int | 20 |

**Response:**

```json
{
  "items": [GameResponse],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 100
  }
}
```

---

### GET `/api/games/liked`

Список игр, которые лайкнул текущий пользователь.

**Query Parameters:**

| Параметр | Тип | По умолчанию |
|----------|-----|-------------|
| page | int | 1 |
| limit | int | 20 |

**Response:** Пагинированный список `GameResponse`

---

### GET `/api/games/my`

Список игр текущего пользователя.

**Query Parameters:**

| Параметр | Тип | По умолчанию |
|----------|-----|-------------|
| page | int | 1 |
| limit | int | 20 |

**Response:** Пагинированный список `GameResponse`

---

### GET `/api/games/{id}`

Получение игры по ID.

**Path Parameters:** `id` — int

**Response:** `GameResponse`

---

### PUT|PATCH `/api/games/{id}`

Обновление игры. Доступно только автору.

**Path Parameters:** `id` — int

**Request Body:**

| Поле | Тип | Обязательное | Ограничения |
|------|-----|-------------|-------------|
| title | string \| null | Нет | min 3, max 255 |
| description | string \| null | Нет | min 10 |
| age | int \| null | Нет | min 3, max 80 |
| players | int \| null | Нет | min 1, max 500 |
| duration | int \| null | Нет | positive |
| locationType | string \| null | Нет | `indoor`, `outdoor`, `both` |
| fieldWidth | int \| null | Нет | min 1, max 1000 |
| fieldLength | int \| null | Нет | min 1, max 1000 |
| activityLevel | string \| null | Нет | `low`, `medium`, `high` |
| requisites | array \| null | Нет | array |
| isPublic | bool \| null | Нет | boolean |
| locationDescription | string \| null | Нет | min 10 |

**Response:** `GameResponse`

---

### DELETE `/api/games/{id}`

Удаление игры. Доступно только автору.

**Path Parameters:** `id` — int

**Response:** 204

---

## Лайки игр

### POST `/api/games/{id}/like`

Лайк игры.

**Path Parameters:** `id` — int

**Response:** 201

---

### DELETE `/api/games/{id}/like`

Удаление лайка.

**Path Parameters:** `id` — int

**Response:** 204

---

## Этапы игры

### POST `/api/games/{gameId}/stages`

Создание этапа игры.

**Path Parameters:** `gameId` — int

**Request Body:**

| Поле | Тип | Обязательное | Ограничения |
|------|-----|-------------|-------------|
| title | string | Да | min 3, max 255 |
| description | string | Да | min 10 |
| duration | int | Да | positive |
| tasks | string[] \| null | Нет | array |
| props | string[] | Нет | array |

**Response:** `GameStageResponse` (201)

---

### PUT|PATCH `/api/games/{gameId}/stages/{id}`

Обновление этапа.

**Path Parameters:** `gameId` — int, `id` — int

**Request Body:**

| Поле | Тип | Обязательное | Ограничения |
|------|-----|-------------|-------------|
| title | string \| null | Нет | min 3, max 255 |
| description | string \| null | Нет | min 10 |
| duration | int \| null | Нет | positive |
| tasks | string[] \| null | Нет | array |
| props | string[] \| null | Нет | array |

**Response:** `GameStageResponse`

---

### DELETE `/api/games/{gameId}/stages/{id}`

Удаление этапа.

**Path Parameters:** `gameId` — int, `id` — int

**Response:** 204

---

## Комментарии к играм

### GET `/api/games/{gameId}/comments`

Список комментариев к игре.

**Path Parameters:** `gameId` — int

**Query Parameters:**

| Параметр | Тип | По умолчанию |
|----------|-----|-------------|
| page | int | 1 |
| limit | int | 20 |

**Response:** Пагинированный список `GameCommentResponse`

---

### POST `/api/games/{gameId}/comments`

Создание комментария.

**Path Parameters:** `gameId` — int

**Request Body:**

| Поле | Тип | Обязательное | Ограничения |
|------|-----|-------------|-------------|
| text | string | Да | min 1, max 1000 |
| parentId | int \| null | Нет | positive |

**Response:** `GameCommentResponse` (201)

---

### PUT|PATCH `/api/games/{gameId}/comments/{id}`

Обновление комментария.

**Path Parameters:** `gameId` — int, `id` — int

**Request Body:**

| Поле | Тип | Обязательное | Ограничения |
|------|-----|-------------|-------------|
| text | string | Да | min 1, max 1000 |

**Response:** `GameCommentResponse`

---

### DELETE `/api/games/{gameId}/comments/{id}`

Удаление комментария.

**Path Parameters:** `gameId` — int, `id` — int

**Response:** 204

---

## Тикеты поддержки

### POST `/api/tickets`

Создание тикета.

**Request Body:**

| Поле | Тип | Обязательное | Ограничения |
|------|-----|-------------|-------------|
| subject | string | Да | not blank, max 255 |
| description | string | Да | not blank, max 5000 |

**Response:** `TicketResponse` (201)

---

### GET `/api/tickets`

Список тикетов текущего пользователя.

**Query Parameters:**

| Параметр | Тип | По умолчанию |
|----------|-----|-------------|
| page | int | 1 |
| limit | int | 20 |
| status | string \| null | null |

`status` — один из: `open`, `in_progress`, `waiting_for_user`, `resolved`, `closed`

**Response:** Пагинированный список `TicketResponse`

---

### GET `/api/tickets/{id}`

Получение тикета.

**Path Parameters:** `id` — int

**Response:** `TicketResponse`

---

### POST `/api/tickets/{id}/take`

Взять тикет в работу.

**Path Parameters:** `id` — int

**Response:** `TicketResponse`

---

### POST `/api/tickets/{id}/status`

Изменить статус тикета.

**Path Parameters:** `id` — int

**Request Body:**

| Поле | Тип | Обязательное | Ограничения |
|------|-----|-------------|-------------|
| status | string | Да | `open`, `in_progress`, `waiting_for_user`, `resolved`, `closed` |

**Response:** `TicketResponse`

---

### POST `/api/tickets/{id}/priority`

Изменить приоритет тикета.

**Path Parameters:** `id` — int

**Request Body:**

| Поле | Тип | Обязательное | Ограничения |
|------|-----|-------------|-------------|
| priority | string | Да | `low`, `medium`, `high`, `urgent` |

**Response:** `TicketResponse`

---

### POST `/api/tickets/{id}/close`

Закрыть тикет.

**Path Parameters:** `id` — int

**Response:** `TicketResponse`

---

## Сообщения тикетов

### GET `/api/tickets/{ticketId}/messages`

Список сообщений тикета.

**Path Parameters:** `ticketId` — int

**Query Parameters:**

| Параметр | Тип | По умолчанию |
|----------|-----|-------------|
| page | int | 1 |
| limit | int | 50 |

**Response:** Пагинированный список `TicketMessageResponse`

---

### POST `/api/tickets/{ticketId}/messages`

Создание сообщения в тикете. Необходимо указать текст или фото (или оба).

**Path Parameters:** `ticketId` — int

**Request Body:**

| Поле | Тип | Обязательное | Ограничения |
|------|-----|-------------|-------------|
| text | string | Условно | max 5000. Обязательно, если `photos` пустой |
| photos | string[] | Условно | каждый: base64 image. Обязательно, если `text` пустой |

**Response:** `TicketMessageResponse` (201)

---

### PATCH `/api/tickets/{ticketId}/messages/{id}`

Обновление сообщения.

**Path Parameters:** `ticketId` — int, `id` — int

**Request Body:**

| Поле | Тип | Обязательное | Ограничения |
|------|-----|-------------|-------------|
| text | string | Да | min 1, max 5000 |
| photos | string[] | Нет | каждый: base64 image |

**Response:** `TicketMessageResponse`

---

### DELETE `/api/tickets/{ticketId}/messages/{id}`

Удаление сообщения.

**Path Parameters:** `ticketId` — int, `id` — int

**Response:** 204

---

## DTO Types

### UserResponse

| Поле | Тип |
|------|-----|
| id | int |
| name | string \| null |
| lastName | string \| null |
| middleName | string \| null |
| email | string |
| login | string \| null |
| avatar | string \| null |
| roles | string[] |
| isActive | bool |
| isBlocked | bool |
| isVerified | bool |

### UserSettingsResponse

| Поле | Тип |
|------|-----|
| generationModel | string \| null (enum) |
| generationCreative | float \| null |

### GameResponse

| Поле | Тип |
|------|-----|
| id | int |
| title | string \| null |
| description | string \| null |
| author | UserResponse \| null |
| age | int \| null |
| players | int \| null |
| duration | int \| null |
| locationType | string \| null (`indoor`, `outdoor`, `both`) |
| fieldWidth | int \| null |
| fieldLength | int \| null |
| activityLevel | string \| null (`low`, `medium`, `high`) |
| photos | string[] \| null |
| requisites | string[] \| null |
| isPublic | bool \| null |
| stages | GameStageResponse[] \| null |
| commentsCount | int \| null |
| likesCount | int \| null |
| isLiked | bool \| null |
| createdAt | string (ISO 8601) |
| updatedAt | string \| null (ISO 8601) |
| locationDescription | string \| null |

### GameStageResponse

| Поле | Тип |
|------|-----|
| id | int |
| order | int |
| title | string \| null |
| description | string \| null |
| duration | int \| null |
| tasks | string[] \| null |
| props | string[] \| null |
| createdAt | string (ISO 8601) |

### GameCommentResponse

| Поле | Тип |
|------|-----|
| id | int \| null |
| gameId | int \| null |
| author | UserResponse \| null |
| text | string \| null |
| parentId | int \| null |
| createdAt | string (ISO 8601) |
| updatedAt | string \| null (ISO 8601) |

### TicketResponse

| Поле | Тип |
|------|-----|
| id | int |
| subject | string |
| description | string \| null |
| status | string (`open`, `in_progress`, `waiting_for_user`, `resolved`, `closed`) |
| priority | string (`low`, `medium`, `high`, `urgent`) |
| author | UserResponse |
| assignedTo | UserResponse \| null |
| createdAt | string (ISO 8601) |
| updatedAt | string \| null (ISO 8601) |
| closedAt | string \| null (ISO 8601) |

### TicketMessageResponse

| Поле | Тип |
|------|-----|
| id | int |
| text | string \| null |
| photos | string[] \| null |
| messageType | string (`user`, `support`, `system`) |
| owner | UserResponse \| null |
| createdAt | string (ISO 8601) |
| updatedAt | string \| null (ISO 8601) |

---

## Enums

### GameLocationType
- `indoor` — В помещении
- `outdoor` — На улице
- `both` — Совмещенно

### GameActivityLevel
- `low` — Низкая
- `medium` — Средняя
- `high` — Высокая

### ModelType (облачные модели для генерации)
- `qwen/qwen3.6-27b`
- `qwen/qwen3.5-27b`
- `qwen/qwen3.6-plus`

Резервная модель (Ollama fallback): `qwen/qwen3.6-plus`

### TicketStatus
- `open` — Открыт
- `in_progress` — В обработке
- `waiting_for_user` — Ожидает ответа пользователя
- `resolved` — Решен
- `closed` — Закрыт

### TicketPriority
- `low` — Низкий
- `medium` — Средний
- `high` — Высокий
- `urgent` — Срочный

### TicketMessageType
- `user` — Сообщение пользователя
- `support` — Сообщение поддержки
- `system` — Системное сообщение

---

## Error Codes

| Код | HTTP | Описание |
|-----|------|----------|
| EMAIL_EXIST | 422 | Пользователь с таким email уже существует |
| LOGIN_EXIST | 422 | Пользователь с таким логином уже существует |
| LIKE_EXIST | 422 | Эта игра уже в избранном |
| VALIDATION_FAILED | 422 | Ошибка валидации данных |
| TICKET_ALREADY_CLOSED | 422 | Тикет уже закрыт |
| UNAUTHORIZED | 401 | Требуется авторизация |
| INVALID_CREDENTIALS | 401 | Неверный email или пароль |
| TOKEN_EXPIRED | 401 | Устаревший токен |
| TOKEN_INVALID | 401 | Некорректный токен |
| TOKEN_MISSING | 401 | Отсутствует токен авторизации |
| FORBIDDEN | 403 | Доступ запрещен |
| NOT_FOUND | 404 | Ресурс не найден |
| USER_NOT_FOUND | 404 | Такого пользователя нет |
| GAME_NOT_FOUND | 404 | Такой игры нет |
| STAGE_NOT_FOUND | 404 | Такой стадии нет |
| COMMENT_NOT_FOUND | 404 | Такого комментария нет |
| LIKE_NOT_FOUND | 404 | Такого лайка нет |
| GENERATION_FAILED | 400 | Ошибка генерации игры |
| INTERNAL_ERROR | 400 | Внутренняя ошибка сервера |
