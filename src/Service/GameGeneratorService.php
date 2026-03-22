<?php

namespace App\Service;

use App\Contracts\Service\GameGeneratorServiceInterface;
use App\DTO\Requests\GenerateGameRequest;
use App\Entity\Game;
use App\Entity\Stage;
use App\Entity\User;
use App\Enum\ErrorCode;
use App\Enum\GameLocationType;
use App\Enum\UploadType;
use App\Exception\ApiException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Сервис генерации игр через AI
 */
class GameGeneratorService implements GameGeneratorServiceInterface
{
    private const API_URL = 'https://routerai.ru/api/v1';
    private const MODEL = 'qwen/qwen3-vl-8b-thinking';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EntityManagerInterface $entityManager,
        private readonly string $aiApiKey,
        private readonly UploadService $uploadService,
    ) {}

    /**
     * Сгенерировать игру через AI и сохранить
     */
    public function generateAndSave(GenerateGameRequest $request, User $author): Game
    {
        // Сохраняем фото перед отправкой в AI
        $savedPhotos = $this->saveRequestPhotos($request, $author->getId());

        // Отправляем в AI с сохраненными фото
        $aiData = $this->generateGameData($request);

        // Сохраняем игру с путями к фото
        return $this->createGameFromAiData($aiData, $author, $request, $savedPhotos);
    }

    /**
     * Сгенерировать данные игры через AI
     * 
     * @return array Данные игры в формате AI
     */
    public function generateGameData(GenerateGameRequest $request): array
    {
        $userContent = [
            [
                'type' => 'text',
                'text' => $this->buildPrompt($request)
            ]
        ];

        // Используем сохраненные фото
        foreach ($request->photos as $photo) {
            $userContent[] = [
                'type' => 'image_url',
                'image_url' => [
                    'url' => $photo
                ]
            ];
        }

        $response = $this->httpClient->request('POST', self::API_URL . '/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->aiApiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => self::MODEL,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Ты преподаватель детей, вожатый в лагере, воспитатель в детском саду с опытом более 10 лет. Проанализируй фото местности и на основе особенностей местности делай все. Отвечай только в JSON.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $userContent
                    ]
                ],
                'temperature' => 0.8,
                'max_tokens' => 2000,
                'response_format' => ['type' => 'json_object'],
            ],
            'timeout' => 60,
        ]);

        $data = $response->toArray();
        $content = $data['choices'][0]['message']['content'] ?? '{}';
        $result = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException(ErrorCode::GENERATION_FAILED);
        }

        return $result;
    }

    /**
     * Сохраняет фотографии из запроса
     * 
     * @return array<string> Массив путей к сохраненным фото
     */
    public function saveRequestPhotos(GenerateGameRequest $request, int $authorId): array
    {
        $savedPaths = [];

        foreach ($request->photos as $index => $photo) {
            $path = $this->uploadService->uploadFromBase64(
                $photo,
                UploadType::REQUEST_PHOTO,
                $authorId,
            );
            $savedPaths[] = $path;
        }

        return $savedPaths;
    }

    /**
     * Построить промпт для AI
     */
    private function buildPrompt(GenerateGameRequest $request): string
    {
        $propsText = '';
        if (!empty($request->requisites)) {
            $propsList = implode(', ', $request->requisites);
            $propsText = "Доступный реквизит: $propsList";
        } else {
            $propsText = "Реквизит не указан, придумай игры без специального реквизита";
        }

        return <<<PROMPT
Создай игру для детей {$request->minAge}-{$request->maxAge} лет.
Игроков: {$request->minPlayers}-{$request->maxPlayers}
Длительность: {$request->duration} минут
Локация: {$request->locationType}
{$propsText}

Проанализируй фото местности и создай игру из 3-5 этапов.

Формат ответа JSON:
{
    "title": "Название игры",
    "description": "Описание",
    "stages": [
        {
            "title": "Название этапа",
            "description": "Описание",
            "duration": 10,
            "tasks": ["задача 1", "задача 2"],
            "props": ["реквизит 1", "реквизит 2"]
        }
    ]
}
PROMPT;
    }

    /**
     * Создать игру из данных AI
     */
    private function createGameFromAiData(array $aiData, User $author, GenerateGameRequest $request, array $savedPhotos): Game
    {
        $game = new Game();
        $game->setTitle($aiData['title'] ?? 'Без названия');
        $game->setDescription($aiData['description'] ?? '');
        $game->setAuthor($author);
        $game->setMinAge($request->minAge);
        $game->setMaxAge($request->maxAge);
        $game->setMinPlayers($request->minPlayers);
        $game->setMaxPlayers($request->maxPlayers);
        $game->setDuration($request->duration);
        $game->setLocationType(GameLocationType::from($request->locationType));
        $game->setRequisites($request->requisites);
        $game->setIsPublic(false);
        $game->setPhotos($savedPhotos);

        $this->entityManager->persist($game);
        $this->entityManager->flush();

        foreach ($aiData['stages'] ?? [] as $index => $stageData) {
            $stage = new Stage();
            $stage->setGame($game);
            $stage->setStageOrder($index + 1);
            $stage->setTitle($stageData['title'] ?? 'Этап ' . ($index + 1));
            $stage->setDescription($stageData['description'] ?? '');
            $stage->setDuration($stageData['duration'] ?? 10);
            $stage->setTasks($stageData['tasks'] ?? []);
            $stage->setProps($stageData['props'] ?? []);

            $this->entityManager->persist($stage);
        }

        $this->entityManager->flush();
        $this->entityManager->refresh($game);

        return $game;
    }
}
