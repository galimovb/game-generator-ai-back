<?php

namespace App\Enum;

enum TicketStatus: string
{
    case OPEN = 'open';
    case IN_PROGRESS = 'in_progress';
    case WAITING_FOR_USER = 'waiting_for_user';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';

    public function getLabel(): string
    {
        return match($this) {
            self::OPEN => 'Открыт',
            self::IN_PROGRESS => 'В обработке',
            self::WAITING_FOR_USER => 'Ожидает ответа пользователя',
            self::RESOLVED => 'Решен',
            self::CLOSED => 'Закрыт',
        };
    }
}