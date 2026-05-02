<?php

namespace App\Game\Service;

use App\Game\DTO\Request\GenerateGameRequest;
use App\Game\Entity\Game;
use App\Game\Entity\GameStage;
use App\Shared\Enum\ActivityLevel;
use App\Shared\Enum\ErrorCode;
use App\Shared\Enum\GameLocationType;
use App\Shared\Enum\UploadType;
use App\Shared\Exception\ApiException;
use App\Shared\Service\UploadService;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GameGenerationService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EntityManagerInterface $entityManager,
        private readonly string $aiApiKey,
        private readonly string $aiApiUrl,
        private readonly UploadService $uploadService,
        private readonly LoggerInterface $logger,
    ) {}

    public function generateAndSave(GenerateGameRequest $request, User $author): Game
    {
        $settings = $author->getUserSettings();
        $model = $settings->getGenerationModel()->value;
        $creative = $settings->getGenerationCreative();

        $savedPhotos = $this->saveRequestPhotos($request, $author->getId());
        $aiData = $this->callVLM($request, $savedPhotos, $model, $creative);

        return $this->saveGame($aiData, $author, $request, $savedPhotos);
    }

    private function saveRequestPhotos(GenerateGameRequest $request, int $authorId): array
    {
        $savedPaths = [];

        foreach ($request->photos as $photo) {
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
        $prompt = $this->buildPrompt($request);
        $userContent = [
            [
                'type' => 'text',
                'text' => $prompt
            ]
        ];

        foreach ($requestPhotos as $photo) {
            $userContent[] = [
                'type' => 'image_url',
                'image_url' => [
                    'url' => $photo
                ]
            ];
        }

        $this->logger->info('AI Generation Request', [
            'age' => $request->age,
            'players' => $request->players,
            'duration' => $request->duration,
            'locationType' => $request->locationType,
            'fieldWidth' => $request->fieldWidth,
            'fieldLength' => $request->fieldLength,
            'activityLevel' => $request->activityLevel,
            'model' => $model,
            'creative' => $creative,
            'photos_count' => count($requestPhotos),
        ]);

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
                'max_tokens' => 4000,
                'response_format' => ['type' => 'json_object'],
            ],
            'timeout' => 120,
        ]);

        $data = $response->toArray();
        $content = $data['choices'][0]['message']['content'] ?? '{}';

        $this->logger->info('AI Response', [
            'content_length' => strlen($content),
            'content_preview' => substr($content, 0, 500)
        ]);

        $result = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('Invalid JSON from AI', [
                'error' => json_last_error_msg(),
                'content' => substr($content, 0, 1000)
            ]);
            throw new ApiException(ErrorCode::GENERATION_FAILED);
        }

        return $result;
    }

    private function buildPrompt(GenerateGameRequest $request): string
    {
        $examples = $this->getRandomExamples();
        $examplesJson = json_encode($examples, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $requisitesText = !empty($request->requisites)
            ? implode(', ', $request->requisites)
            : 'не указан';

        $activityLevelText = match($request->activityLevel) {
            'low' => 'Низкая (спокойные, интеллектуальные игры, минимум бега)',
            'medium' => 'Средняя (умеренный бег, эстафеты, подвижные игры)',
            'high' => 'Высокая (интенсивный бег, прыжки, высокая физическая нагрузка)',
            default => 'Средняя'
        };

        return <<<PROMPT
ПАРАМЕТРЫ:
- Возраст детей: {$request->age} лет
- Количество игроков: {$request->players} человек
- Длительность: {$request->duration} минут
- Локация: {$request->locationType}
- Размер площадки: {$request->fieldWidth} x {$request->fieldLength} метров
- Уровень активности: {$activityLevelText}
- Доступный реквизит: {$requisitesText}

ПРИМЕРЫ ХОРОШИХ ИГР (строго соблюдай структуру):
{$examplesJson}

ПРАВИЛА:
1. Сумма длительности всех этапов = {$request->duration} минут
2. Количество этапов: от 1 до 3 (если игра до 15 минут — 1 этап)
3. Каждый этап должен отличаться по механике
4. Сюжет должен быть единым для всей игры
5. Описания должны быть понятны любому ведущему, четко описана суть каждого этапа
6. Игра должна быть физически выполнима для возрастной группы. Учитывай указанный уровень активности
7. Учитывай размер площадки — не предлагай задания, требующие больше места, чем есть
8. Для {$request->players} игроков предложи конкретное распределение по ролям или командам
9. Названия игры и этапов должны быть четкими и понятными
10. Используй для игры только ту зону, которая указана на фотографии
11. Делай правильное склонение слов в тексте

ФОРМАТ ОТВЕТА (ТОЛЬКО JSON, БЕЗ ПОЯСНЕНИЙ):
{
    "title": "название игры",
    "description": "описание сюжета и хода игры (4-6 предложений)",
    "stages": [
        {
            "title": "название этапа",
            "description": "подробное описание: что делают дети, что делает ведущий, как определить результат (6-10 предложений)",
            "duration": "число, минут",
            "tasks": ["конкретное действие игрока 1", "действие 2", "действие 3"],
            "props": ["какой реквизит нужен"],
            "stage_goal": "что получают дети после этапа"
        }
    ]
}

ВЕРНИ ТОЛЬКО JSON. НИКАКОГО ДРУГОГО ТЕКСТА.
PROMPT;
    }

    private function getRandomExamples(): array
    {
        $path = __DIR__ . '/../train/base.json';

        if (!file_exists($path)) {
            return [];
        }

        $content = file_get_contents($path);
        $games = json_decode($content, true);

        if (!is_array($games) || empty($games)) {
            return [];
        }

        shuffle($games);
        return array_slice($games, 0, 7);
    }

    private function saveGame(array $aiData, User $author, GenerateGameRequest $request, array $savedPhotos): Game
    {
        $game = new Game();
        $game->setTitle($aiData['title'] ?? 'Без названия');
        $game->setDescription($aiData['description'] ?? '');
        $game->setAuthor($author);
        $game->setAge($request->age);
        $game->setPlayers($request->players);
        $game->setDuration($request->duration);
        $game->setLocationType(GameLocationType::from($request->locationType));
        $game->setFieldWidth($request->fieldWidth);
        $game->setFieldLength($request->fieldLength);
        $game->setActivityLevel(ActivityLevel::from($request->activityLevel));
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