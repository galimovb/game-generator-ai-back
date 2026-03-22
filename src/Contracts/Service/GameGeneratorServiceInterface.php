<?php

namespace App\Contracts\Service;

use App\DTO\Requests\GenerateGameRequest;
use App\Entity\Game;
use App\Entity\User;

/**
 * Интерфейс сервиса генерации игр через AI
 */
interface GameGeneratorServiceInterface
{
    /**
     * Сгенерировать игру через AI и сохранить
     */
    public function generateAndSave(GenerateGameRequest $request, User $author): Game;

    /**
     * Сгенерировать данные игры через AI
     * 
     * @return array Данные игры в формате AI
     */
    public function generateGameData(GenerateGameRequest $request): array;

    /**
     * Сохранить фотографии из запроса
     * 
     * @return array<string> Массив путей к сохраненным фото
     */
    public function saveRequestPhotos(GenerateGameRequest $request, int $authorId): array;
}
