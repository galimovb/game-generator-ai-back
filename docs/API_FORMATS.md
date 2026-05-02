# API Форматы ответов, ошибок и валидация

---

## 1. Универсальный формат ответа

### Успех — `ApiResponse::success()`

```json
{
  "result": <mixed | null>,
  "timestamp": 1744080000
}
```

**HTTP статусы:** `200` (по умолчанию), `201` (создание), `204` (удаление)

**Варианты `result`:**

| Сценарий | Тип `result` | HTTP |
|----------|-------------|------|
| Один объект | DTO-объект | 200 / 201 |
| Список (пагинация) | `{ items: [...], pagination: { page, limit, total } }` | 200 |
| Удаление | `true` или `null` | 204 |
| Like/Unlike | `null` | 201 / 204 |

---

### Ошибка — `ApiResponse::error()`

**ApiException (бизнес-ошибки):**
```json
{
  "error": "ERROR_CODE",
  "errorMessage": "Человекочитаемое сообщение"
}
```

**Неизвестная ошибка (Throwable):**
```json
{
  "error": "INTERNAL_ERROR",
  "errorMessage": "Исходное сообщение исключения"
}
```

---

## 2. Коды ошибок (`ErrorCode` enum)

| Код | Сообщение | HTTP | Когда возникает |
|-----|-----------|------|-----------------|
| `EMAIL_EXIST` | Пользователь с таким email уже существует | **422** | Регистрация с занятым email |
| `LOGIN_EXIST` | Пользователь с таким логином уже существует | **422** | Регистрация/обновление профиля с занятым логином |
| `LIKE_EXIST` | Эта игра уже в избранном | **422** | Повторный лайк |
| `VALIDATION_FAILED` | Ошибка валидации данных | **422** | Несоответствие типов/ограничений |
| `GENERATION_FAILED` | Ошибка генерации игры | **422** | Ошибка ИИ при генерации |
| `TICKET_ALREADY_CLOSED` | Тикет уже закрыт | **422** | Операции над закрытым тикетом |
| `UNAUTHORIZED` | Требуется авторизация | **401** | Отсутствие JWT |
| `INVALID_CREDENTIALS` | Неверный email или пароль | **401** | Ошибка логина |
| `TOKEN_EXPIRED` | Устаревший токен | **401** | JWT просрочен |
| `TOKEN_INVALID` | Некорректный токен | **401** | JWT невалиден |
| `TOKEN_MISSING` | Отсутствует токен авторизации | **401** | JWT не передан |
| `FORBIDDEN` | Доступ запрещен | **403** | Нет прав на действие |
| `NOT_FOUND` | Ресурс не найден | **404** | Общий случай |
| `USER_NOT_FOUND` | Такого пользователя нет | **404** | Пользователь по ID не найден |
| `GAME_NOT_FOUND` | Такой игры нет | **404** | Игра по ID не найдена |
| `STAGE_NOT_FOUND` | Такой стадии нет | **404** | Стадия по ID не найдена |
| `COMMENT_NOT_FOUND` | Такого комментария нет | **404** | Комментарий по ID не найден |
| `LIKE_NOT_FOUND` | Такого лайка нет | **404** | Попытка удалить несуществующий лайк |
| `INTERNAL_ERROR` | Внутренняя ошибка сервера | **500** | Любое непойманное исключение |

---

## 3. Response DTO (форматы объектов в `result`)

### 3.1 UserResponse

```json
{
  "id": 1,
  "name": "Иван",
  "lastName": "Петров",
  "middleName": "Сергеевич",
  "email": "ivan@example.com",
  "login": "ivan_petrov",
  "avatar": "/uploads/avatars/...jpg",
  "roles": ["ROLE_USER"],
  "isActive": true,
  "isBlocked": false,
  "isVerified": true
}
```

### 3.2 UserSettingsResponse

```json
{
  "generationModel": "qwen/qwen2.5-vl-7b-instruct",
  "generationCreative": 0.7
}
```

### 3.3 GameResponse

```json
{
  "id": 42,
  "title": "Захват флага",
  "description": "Командная игра...",
  "author": { /* UserResponse */ },
  "minAge": 10,
  "maxAge": 16,
  "minPlayers": 8,
  "maxPlayers": 20,
  "duration": 60,
  "locationType": "outdoor",
  "photos": ["/uploads/games/photo1.jpg"],
  "requisites": ["флаги", "маркеры"],
  "isPublic": true,
  "stages": [/* GameStageResponse[] */],
  "commentsCount": 5,
  "likesCount": 12,
  "isLiked": false,
  "createdAt": "2026-04-07T18:00:00+03:00",
  "updatedAt": "2026-04-07T19:30:00+03:00"
}
```

### 3.4 GameStageResponse

```json
{
  "id": 1,
  "order": 0,
  "title": "Разминка",
  "description": "Участники делятся на команды...",
  "duration": 10,
  "tasks": ["задача 1", "задача 2"],
  "props": { "key": "value" },
  "createdAt": "2026-04-07T18:00:00+03:00"
}
```

### 3.5 GameCommentResponse

```json
{
  "id": 10,
  "gameId": 42,
  "author": { /* UserResponse */ },
  "text": "Отличная игра!",
  "parentId": null,
  "createdAt": "2026-04-07T18:00:00+03:00",
  "updatedAt": null
}
```

### 3.6 TicketResponse

```json
{
  "id": 5,
  "subject": "Не работает генерация",
  "description": "При попытке создать игру выдает ошибку...",
  "status": "open",
  "priority": "medium",
  "author": { /* UserResponse */ },
  "assignedTo": { /* UserResponse */ | null },
  "createdAt": "2026-04-07T18:00:00+03:00",
  "updatedAt": "2026-04-07T19:00:00+03:00",
  "closedAt": null
}
```

### 3.7 TicketMessageResponse

```json
{
  "id": 15,
  "text": "Проверю логи",
  "photos": ["/uploads/tickets/img.jpg"],
  "messageType": "user",
  "owner": { /* UserResponse */ },
  "createdAt": "2026-04-07T18:05:00+03:00",
  "updatedAt": null
}
```

---

## 4. Request DTO (валидация входящих данных)

### 4.1 RegisterUserRequest → `POST /api/auth/register`

| Поле | Тип | Обязательно | Ограничения |
|------|-----|-------------|-------------|
| `email` | string | ✅ | Валидный email |
| `password` | string | ✅ | min 8 символов |
| `login` | string | ❌ | max 255 |
| `name` | string | ❌ | max 255 |
| `lastName` | string | ❌ | max 255 |
| `middleName` | string | ❌ | max 255 |

### 4.2 UpdateProfileRequest → `PUT/PATCH /api/users/profile`

| Поле | Тип | Обязательно | Ограничения |
|------|-----|-------------|-------------|
| `name` | string | ❌ | min 2, max 255 |
| `lastName` | string | ❌ | min 2, max 255 |
| `middleName` | string | ❌ | min 2, max 255 |
| `email` | string | ❌ | Валидный email, max 255 |
| `login` | string | ❌ | min 3, max 50, regex: `^[a-zA-Z0-9_]+$` |
| `avatar` | string | ❌ | base64 image: regex `^data:image\/(jpeg\|png\|webp);base64,` |

### 4.3 UpdateUserSettingsRequest → `PUT/PATCH /api/users/profile/settings`

| Поле | Тип | Обязательно | Ограничения |
|------|-----|-------------|-------------|
| `generationModel` | string | ✅ | Enum: `qwen/qwen2.5-vl-7b-instruct`, `qwen/qwen3-vl-8b-instruct`, `qwen/qwen3-vl-8b-thinking` |
| `generationCreative` | float | ✅ | range: 0.0 – 1.0 |

### 4.4 GenerateGameRequest → `POST /api/games/generate`

| Поле | Тип | Обязательно | Ограничения |
|------|-----|-------------|-------------|
| `minAge` | int | ✅ | range: 3 – 80 |
| `maxAge` | int | ✅ | range: 3 – 80, **> minAge** |
| `minPlayers` | int | ✅ | range: 1 – 100 |
| `maxPlayers` | int | ✅ | range: 1 – 500, **> minPlayers** |
| `duration` | int | ✅ | range: 5 – 480 (минут) |
| `locationType` | string | ✅ | Enum: `indoor`, `outdoor`, `both` |
| `requisites` | string[] | ❌ | Каждый элемент: string, not blank, max 100 |
| `photos` | string[] | ❌ | Каждый элемент: base64 image, regex `^data:image\/(jpeg\|png\|webp\|gif);base64,` |

### 4.5 UpdateGameRequest → `PUT/PATCH /api/games/{id}`

| Поле | Тип | Обязательно | Ограничения |
|------|-----|-------------|-------------|
| `title` | string | ❌ | min 3, max 255 |
| `description` | string | ❌ | min 10 |
| `minAge` | int | ❌ | range: 1 – 18 |
| `maxAge` | int | ❌ | range: 1 – 18 |
| `minPlayers` | int | ❌ | > 0 |
| `maxPlayers` | int | ❌ | > 0 |
| `duration` | int | ❌ | > 0 |
| `locationType` | string | ✅ | Enum: `indoor`, `outdoor`, `both` |
| `requisites` | array | ❌ | array type |
| `isPublic` | bool | ❌ | boolean |

### 4.6 CreateStageRequest → `POST /api/games/{gameId}/stages`

| Поле | Тип | Обязательно | Ограничения |
|------|-----|-------------|-------------|
| `title` | string | ✅ | min 3, max 255 |
| `description` | string | ✅ | min 10 (без верхнего лимита) |
| `duration` | int | ✅ | > 0 |
| `tasks` | array | ❌ | array type |
| `props` | array | ❌ | array type |

### 4.7 UpdateStageRequest → `PUT/PATCH /api/games/{gameId}/stages/{id}`

| Поле | Тип | Обязательно | Ограничения |
|------|-----|-------------|-------------|
| `title` | string | ❌ | min 3, max 255 |
| `description` | string | ❌ | min 10 |
| `duration` | int | ❌ | > 0 |
| `tasks` | array | ❌ | array type |
| `props` | array | ❌ | array type |

### 4.8 CreateCommentRequest → `POST /api/games/{gameId}/comments`

| Поле | Тип | Обязательно | Ограничения |
|------|-----|-------------|-------------|
| `text` | string | ✅ | min 1, max 1000 |
| `parentId` | int \| null | ❌ | > 0 (если указан) |

### 4.9 UpdateCommentRequest → `PUT/PATCH /api/games/{gameId}/comments/{id}`

| Поле | Тип | Обязательно | Ограничения |
|------|-----|-------------|-------------|
| `text` | string | ✅ | min 1, max 1000 |

### 4.10 CreateTicketRequest → `POST /api/tickets`

| Поле | Тип | Обязательно | Ограничения |
|------|-----|-------------|-------------|
| `subject` | string | ✅ | max 255 |
| `description` | string | ✅ | max 5000 |

### 4.11 CreateTicketMessageRequest → `POST /api/tickets/{ticketId}/messages`

| Поле | Тип | Обязательно | Ограничения |
|------|-----|-------------|-------------|
| `text` | string | ❌ | max 5000 |
| `photos` | string[] | ❌ | Каждый элемент: base64 image, regex `^data:image\/(jpeg\|png\|webp\|gif);base64,` |

**Кастомная валидация:** хотя бы одно из полей `text` или `photos` должно быть заполнено.

### 4.12 UpdateTicketMessageRequest → `PATCH /api/tickets/{ticketId}/messages/{id}`

| Поле | Тип | Обязательно | Ограничения |
|------|-----|-------------|-------------|
| `text` | string | ✅ | min 1, max 5000 |
| `photos` | string[] | ❌ | Каждый элемент: base64 image, regex `^data:image\/(jpeg\|png\|webp\|gif);base64,` |

### 4.13 ChangeTicketStatusRequest → `POST /api/tickets/{id}/status`

| Поле | Тип | Обязательно | Ограничения |
|------|-----|-------------|-------------|
| `status` | string | ✅ | Enum: `open`, `in_progress`, `waiting_for_user`, `resolved`, `closed` |

### 4.14 ChangeTicketPriorityRequest → `POST /api/tickets/{id}/priority`

| Поле | Тип | Обязательно | Ограничения |
|------|-----|-------------|-------------|
| `priority` | string | ✅ | Enum: `low`, `medium`, `high`, `urgent` |

---

## 5. Enum-значения для справки

### GameLocationType
| Value | Описание |
|-------|---------|
| `indoor` | В помещении |
| `outdoor` | На улице |
| `both` | Совмещённо |

### ModelType
| Value |
|-------|
| `qwen/qwen2.5-vl-7b-instruct` |
| `qwen/qwen3-vl-8b-instruct` |
| `qwen/qwen3-vl-8b-thinking` |

### TicketStatus
| Value | Label |
|-------|-------|
| `open` | Открыт |
| `in_progress` | В обработке |
| `waiting_for_user` | Ожидает ответа пользователя |
| `resolved` | Решён |
| `closed` | Закрыт |

### TicketPriority
| Value | Label |
|-------|-------|
| `low` | Низкий |
| `medium` | Средний |
| `high` | Высокий |
| `urgent` | Срочный |

### TicketMessageType
| Value |
|-------|
| `user` |
| `staff` |

---

## 6. Query-параметры пагинации (GET-эндпоинты со списками)

| Параметр | Тип | По умолчанию |
|----------|-----|-------------|
| `page` | int | 1 |
| `limit` | int | 20 (50 для сообщений тикетов) |

**Дополнительно:** `?status=<string>` — фильтр по статусу для списка тикетов.
