<?php

namespace App\Game\Service;

use App\Game\DTO\Request\GenerateGameRequest;
use App\Game\Entity\Game;
use App\Game\Entity\GameStage;
use App\Shared\Enum\ErrorCode;
use App\Shared\Enum\GameLocationType;
use App\Shared\Enum\UploadType;
use App\Shared\Exception\ApiException;
use App\Shared\Service\UploadService;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GameGenerationService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EntityManagerInterface $entityManager,
        private readonly string $aiApiKey,
        private readonly string $aiApiUrl,
        private readonly UploadService $uploadService,
    ) {}

    public function generateAndSave(GenerateGameRequest $request, User $author): Game
    {
        // Получаем настройки пользователя (всегда существуют)
        $settings = $author->getUserSettings();
        $model = $settings->getGenerationModel()->value;
        $creative = $settings->getGenerationCreative();

        // Сохраняем фото перед отправкой в AI
        $savedPhotos = $this->saveRequestPhotos($request, $author->getId());

        $aiData = $this->callVLM($request, $request->photos, $model, $creative);

        // Сохраняем игру с путями к фото
        return $this->saveGame($aiData, $author, $request, $savedPhotos);
    }

    /**
     * Сохраняет фотографии из запроса
     * @return array<string> Массив путей к сохраненным фото
     */
    private function saveRequestPhotos(GenerateGameRequest $request, int $authorId): array
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

    private function callVLM(GenerateGameRequest $request, array $requestPhotos, string $model, float $creative): array
    {
        $userContent = [
            [
                'type' => 'text',
                'text' => $this->buildPrompt($request)
            ]
        ];

        // Используем сохраненные фото
        foreach ($requestPhotos as $photo) {
            $userContent[] = [
                'type' => 'image_url',
                'image_url' => [
                    'url' => $photo
                ]
            ];
        }

        $response = $this->httpClient->request('POST', $this->aiApiUrl . '/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->aiApiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $model,
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
                'temperature' => $creative,
                'max_tokens' => 10000,
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

    private function saveGame(array $aiData, User $author, GenerateGameRequest $request, array $savedPhotos): Game
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
            $stage = new GameStage();
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