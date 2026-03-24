<?php

namespace App\Service;

use App\Enum\UploadType;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadService
{
    private string $uploadDirectory;
    private SluggerInterface $slugger;

    public function __construct(
        string $uploadDirectory,
        SluggerInterface $slugger,
    ) {
        $this->uploadDirectory = rtrim($uploadDirectory, '/');
        $this->slugger = $slugger;
    }

    /**
     * Загружает фото из base64
     */
    public function uploadFromBase64(
        string $base64,
        UploadType $type,
        ?int $entityId = null
    ): string {
        // Парсим base64
        if (!preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
            throw new \InvalidArgumentException('Неверный формат base64');
        }

        $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
        $imageData = base64_decode(substr($base64, strpos($base64, ',') + 1), true);

        if ($imageData === false) {
            throw new \InvalidArgumentException('Не удалось декодировать base64');
        }

        // Генерируем хэш имени
        $filename = $this->generateHashedFilename($extension, $type, $entityId);

        // Сохраняем
        return $this->saveFile($imageData, $filename, $type);
    }

    /**
     * Удаляет файл
     */
    public function deleteFile(string $filePath): bool
    {
        $fullPath = $this->uploadDirectory . '/' . ltrim($filePath, '/');
        return file_exists($fullPath) ? unlink($fullPath) : false;
    }

    /**
     * Генерация хэшированного имени файла
     */
    private function generateHashedFilename(string $extension, UploadType $type, ?int $entityId = null): string
    {
        $data = $type->value;
        if ($entityId) {
            $data .= '_' . $entityId;
        }
        $data .= '_' . uniqid('', true);

        // Создаем хэш
        $hash = hash('xxh64', $data); // Быстрый хэш

        return $hash . '.' . $extension;
    }

    /**
     * Сохранение файла из base64
     */
    private function saveFile(string $data, string $filename, UploadType $type): string
    {
        $uploadPath = $this->uploadDirectory . '/' . $type->getPath();

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $filePath = $uploadPath . '/' . $filename;

        if (file_put_contents($filePath, $data) === false) {
            throw new \RuntimeException('Не удалось сохранить файл');
        }

        return '/' . $this->uploadDirectory . '/' . $type->getPath() . '/' . $filename;
    }
}