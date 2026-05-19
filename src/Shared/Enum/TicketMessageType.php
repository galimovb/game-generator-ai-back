<?php

namespace App\Shared\Enum;

enum TicketMessageType: string
{
    case USER = 'user';
    case SUPPORT = 'support';
    case SYSTEM = 'system';
}
