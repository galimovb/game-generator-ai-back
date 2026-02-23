<?php

namespace App\Enum;

enum ErrorCode: string
{
    case EMAIL_EXIST = 'EMAIL_EXIST';
    case LOGIN_EXIST = 'LOGIN_EXIST';
    case VALIDATION_FAILED = 'VALIDATION_FAILED';
    case GENERATION_FAILED = 'GENERATION_FAILED';
    case UNAUTHORIZED = 'UNAUTHORIZED';
    case FORBIDDEN = 'FORBIDDEN';
    case NOT_FOUND = 'NOT_FOUND';
    case INTERNAL_ERROR = 'INTERNAL_ERROR';

    public function getMessage(): string
    {
        return match($this) {
            self::EMAIL_EXIST => 'Пользователь с таким email уже существует',
            self::LOGIN_EXIST => 'Пользователь с таким логином уже существует',
            self::VALIDATION_FAILED => 'Ошибка валидации данных',
            self::GENERATION_FAILED => 'Ошибка генерации игры',
            self::UNAUTHORIZED => 'Требуется авторизация',
            self::FORBIDDEN => 'Доступ запрещен',
            self::NOT_FOUND => 'Ресурс не найден',
            self::INTERNAL_ERROR => 'Внутренняя ошибка сервера',
        };
    }

    public function getHttpCode(): int
    {
        return match($this) {
            self::EMAIL_EXIST, self::LOGIN_EXIST, self::VALIDATION_FAILED => 422,
            self::UNAUTHORIZED => 401,
            self::FORBIDDEN => 403,
            self::NOT_FOUND => 404,
            default => 400,
        };
    }
}