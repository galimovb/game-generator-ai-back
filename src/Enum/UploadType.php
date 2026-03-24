<?php

namespace App\Enum;

enum UploadType: string
{
    case AVATAR = 'avatar';
    case REQUEST_PHOTO = 'request_photo';

    case TICKET_PHOTO = 'ticket_photo';

    public function getPath(): string
    {
        return match($this) {
            self::AVATAR => 'avatars',
            self::REQUEST_PHOTO => 'requests',
            self::TICKET_PHOTO => 'tickets',
        };
    }

    public function getMaxSize(): int
    {
        return match($this) {
            self::AVATAR => 2 * 1024 * 1024,      // 2MB
            self::REQUEST_PHOTO => 10 * 1024 * 1024, // 10MB
            default => 5 * 1024 * 1024,            // 5MB по умолчанию
        };
    }

    public function getAllowedMimeTypes(): array
    {
        return match($this) {
            self::AVATAR => ['image/jpeg', 'image/png', 'image/webp'],
            default => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        };
    }
}