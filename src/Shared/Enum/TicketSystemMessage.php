<?php

namespace App\Shared\Enum;

enum TicketSystemMessage: string
{
    case STATUS_CHANGED = 'status_changed';
    case TAKEN = 'taken';
    case CLOSED = 'closed';

    public function getText(array $context = []): string
    {
        return match($this) {
            self::STATUS_CHANGED => sprintf(
                'Изменён статус тикета%s%s',
                isset($context['old']) ? " с '{$context['old']}'" : '',
                isset($context['new']) ? " на '{$context['new']}'" : ''
            ),
            self::TAKEN => 'Тикет взят в работу',
            self::CLOSED => 'Тикет закрыт',
        };
    }
}