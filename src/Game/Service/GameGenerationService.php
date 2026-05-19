<?php

namespace App\Game\Service;

use App\Game\DTO\JudgeResult;
use App\Game\DTO\Request\GenerateGameRequest;
use App\Game\Entity\Game;
use App\Game\Entity\GameJudgeLog;
use App\Game\Entity\GameStage;
use App\Shared\Enum\ErrorCode;
use App\Shared\Enum\GameActivityLevel;
use App\Shared\Enum\GameLocationType;
use App\Shared\Enum\ModelType;
use App\Shared\Enum\UploadType;
use App\Shared\Exception\ApiException;
use App\Shared\Service\UploadService;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GameGenerationService
{
    private const MAX_JUDGE_RETRIES = 2;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EntityManagerInterface $entityManager,
        private readonly string $aiApiKey,
        private readonly string $aiApiUrl,
        private readonly string $ollamaApiUrl,
        private readonly UploadService $uploadService,
        private readonly LoggerInterface $logger,
        private readonly GameJudgeService $judgeService,
    ) {}

    public function generateAndSave(GenerateGameRequest $request, User $author): Game
    {
        $settings = $author->getUserSettings();
        $model    = $settings->getGenerationModel()->value;
        $creative = $settings->getGenerationCreative();

        $savedPhotos     = $this->saveRequestPhotos($request, $author->getId());
        $judgeFailReason = null;
        $lastJudgeResult = null;

        for ($attempt = 1; $attempt <= self::MAX_JUDGE_RETRIES; $attempt++) {
            $aiData          = $this->callAI($request, $request->photos, $model, $creative, judgeFailReason: $judgeFailReason);
            $judgeResult     = $this->judgeService->evaluate($aiData, $request, $request->photos);
            $lastJudgeResult = $judgeResult;

            if ($judgeResult->passed) {
                $game = $this->saveGame($aiData, $author, $request, $savedPhotos);
                $this->saveJudgeLog($game, $judgeResult, $attempt);
                return $game;
            }

            $judgeFailReason = implode(' ', array_filter([
                $judgeResult->failReason,
                !empty($judgeResult->safetyIssues) ? 'Проблемы безопасности: ' . implode('; ', $judgeResult->safetyIssues) : null,
            ]));

            $this->logger->warning('Judge rejected game, retrying', [
                'attempt'       => $attempt,
                'max'           => self::MAX_JUDGE_RETRIES,
                'is_safe'       => $judgeResult->isSafe,
                'score'         => $judgeResult->score,
                'safety_issues' => $judgeResult->safetyIssues,
                'fail_reason'   => $judgeResult->failReason,
            ]);
        }

        throw new ApiException(
            ($lastJudgeResult?->isSafe === false)
                ? ErrorCode::SAFETY_CHECK_FAILED
                : ErrorCode::GENERATION_FAILED
        );
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

    private function callAI(GenerateGameRequest $request, array $requestPhotos, string $model, float $creative, int $retryCount = 2, ?string $judgeFailReason = null): array
    {
        $prompt = $this->buildPrompt($request, $judgeFailReason);
        $userContent = [
            ['type' => 'text', 'text' => $prompt]
        ];

        foreach ($requestPhotos as $photoBase64) {
            $userContent[] = [
                'type' => 'image_url',
                'image_url' => ['url' => $photoBase64]
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
            'has_location_description' => !empty($request->locationDescription),
        ]);

        $messages = [
            [
                'role' => 'system',
                'content' => $this->buildSystemPrompt($request)
            ],
            [
                'role' => 'user',
                'content' => $userContent
            ]
        ];

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $creative,
            'max_tokens' => 12000,
            'response_format' => ['type' => 'json_object'],
        ];

        for ($attempt = 1; $attempt <= $retryCount; $attempt++) {
            try {
                $result = $this->sendCloudRequest($payload, $attempt);
                $this->validateAiResponse($result);
                return $result;
            } catch (ApiException $e) {
                $this->logger->warning('AI response validation failed, attempt ' . $attempt);
                if ($attempt === $retryCount) {
                    throw $e;
                }
            } catch (\Exception $e) {
                $this->logger->error('AI request failed, attempt ' . $attempt, [
                    'error' => $e->getMessage(),
                ]);
                if ($attempt === $retryCount) {
                    $this->logger->info('Switching to Ollama fallback');
                    try {
                        $result = $this->sendOllamaRequest($payload);
                        $this->validateAiResponse($result);
                        return $result;
                    } catch (\Exception $ollamaEx) {
                        $this->logger->error('Ollama fallback failed', [
                            'error' => $ollamaEx->getMessage(),
                        ]);
                        throw new ApiException(ErrorCode::GENERATION_FAILED);
                    }
                }
            }
        }

        throw new ApiException(ErrorCode::GENERATION_FAILED);
    }

    private function sendCloudRequest(array $payload, int $attempt): array
    {
        $response = $this->httpClient->request('POST', $this->aiApiUrl . '/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->aiApiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
            'timeout' => 120,
        ]);

        $statusCode = $response->getStatusCode();

        if ($statusCode >= 500) {
            $this->logger->error('Cloud AI returned server error', [
                'attempt' => $attempt,
                'status_code' => $statusCode,
            ]);
            throw new \RuntimeException('Cloud AI server error: HTTP ' . $statusCode);
        }

        $data = $response->toArray();
        $content = $data['choices'][0]['message']['content'] ?? '{}';

        $this->logger->info('Cloud AI Response', [
            'attempt' => $attempt,
            'content_length' => strlen($content),
            'content_preview' => substr($content, 0, 500)
        ]);

        $result = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->warning('Invalid JSON from cloud AI, attempt ' . $attempt);
            throw new ApiException(ErrorCode::GENERATION_FAILED);
        }

        return $result;
    }

    private function sendOllamaRequest(array $payload): array
    {
        $fallbackModel = ModelType::fallbackModel();
        $payload['model'] = $fallbackModel;

        $this->logger->info('Sending request to Ollama', [
            'model' => $fallbackModel,
        ]);

        $response = $this->httpClient->request('POST', $this->ollamaApiUrl . '/api/chat/completions', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
            'timeout' => 180,
        ]);

        $statusCode = $response->getStatusCode();

        if ($statusCode >= 400) {
            throw new \RuntimeException('Ollama returned error: HTTP ' . $statusCode);
        }

        $data = $response->toArray();
        $content = $data['choices'][0]['message']['content'] ?? '{}';

        $this->logger->info('Ollama Response', [
            'content_length' => strlen($content),
            'content_preview' => substr($content, 0, 500)
        ]);

        $result = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException(ErrorCode::GENERATION_FAILED);
        }

        return $result;
    }

    private function buildSystemPrompt(GenerateGameRequest $request): string
    {
        $base = 'Ты опытный вожатый с 10+ лет стажа. Твой приоритет — безопасность детей. Создавай только физически безопасные игры для указанного возраста. Отвечай только в JSON.';

        if (!empty($request->photos)) {
            return $base . ' Проанализируй фото местности.';
        }

        if (!empty($request->locationDescription)) {
            return $base . ' Проанализируй описание местности.';
        }

        return $base;
    }

    private function buildPrompt(GenerateGameRequest $request, ?string $judgeFailReason = null): string
    {
        $examples     = $this->getRandomExamples();
        $examplesJson = json_encode($examples, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $requisitesText = !empty($request->requisites)
            ? implode(', ', $request->requisites)
            : 'не указан';

        $activityLevelText = GameActivityLevel::from($request->activityLevel)->getDescription();
        $locationTypeText  = GameLocationType::from($request->locationType)->getValue();

        $locationBlock = '';
        if (!empty($request->locationDescription)) {
            $locationBlock = "\n\nОПИСАНИЕ МЕСТНОСТИ:\n{$request->locationDescription}\n\nОпирайся на это описание при создании игры.\n";
        }

        $safetyConstraints = $this->getAgeSafetyConstraints($request->age);

        $feedbackBlock = '';
        if ($judgeFailReason) {
            $feedbackBlock = "!!! ПРЕДЫДУЩАЯ ИГРА ОТКЛОНЕНА: {$judgeFailReason}\nСоздай ДРУГУЮ игру, устранив все указанные проблемы. !!!\n\n";
        }

        return <<<PROMPT
{$feedbackBlock}ПАРАМЕТРЫ:
- Возраст детей: {$request->age} лет
- Количество игроков: {$request->players} человек
- Длительность: {$request->duration} минут
- Локация: {$locationTypeText}
- Размер площадки: {$request->fieldWidth} x {$request->fieldLength} метров
- Уровень активности: {$activityLevelText}
- Доступный реквизит: {$requisitesText}

ФИЗИЧЕСКИЕ ОГРАНИЧЕНИЯ ДЛЯ {$request->age} ЛЕТ (обязательно соблюдать):
{$safetyConstraints}
{$locationBlock}
ПРИМЕРЫ ХОРОШИХ ИГР (строго соблюдай структуру):
{$examplesJson}

ПРАВИЛА:
1. Сумма длительности всех этапов = {$request->duration} минут
2. Количество этапов: от 1 до 3 (если игра до 15 минут — 1 этап)
3. Каждый этап должен отличаться по механике
4. Сюжет должен быть единым для всей игры
5. Описания должны быть понятны любому ведущему
6. Игра должна быть безопасна согласно ограничениям выше
7. Учитывай размер площадки — не предлагай задания, требующие больше места, чем есть
8. Для {$request->players} игроков предложи конкретное распределение по ролям или командам
9. Делай правильное склонение слов в тексте
10. Если реквизита нет — придумай игру без реквизита или с природным реквизитом

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

    public function getAgeSafetyConstraints(int $age): string
    {
        return match(true) {
            $age <= 5  => "- Бег: не более 2 мин без остановки, дистанция до 20 м\n- Прыжки: только на месте или с места, высота не более 20 см\n- Предметы: не тяжелее 1 кг\n- Запрещено: столкновения, лазанье выше 1 м, спортивные снаряды (кольца, корзины), сложные правила",
            $age <= 8  => "- Бег: до 5 мин, дистанция до 70 м\n- Прыжки: в длину, невысокие препятствия до 40 см; спортивные кольца/корзины ЗАПРЕЩЕНЫ (высота 3+ м недостижима)\n- Предметы: не тяжелее 2 кг; камни и тяжёлые снаряды ЗАПРЕЩЕНЫ\n- Запрещено: прыжки с высоты > 50 см, силовые столкновения, поднятие других детей",
            $age <= 12 => "- Бег: до 10 мин, эстафеты допустимы\n- Прыжки: препятствия до 70 см\n- Предметы: не тяжелее 5 кг\n- Запрещено: поднятие партнёра, прыжки с высоты > 1 м, тяжёлые снаряды",
            $age <= 17 => "- Интенсивные нагрузки допустимы\n- Предметы: до 10 кг\n- Запрещено: жёсткий силовой контакт, высоты без страховки",
            default    => "- Стандартные нагрузки, умеренный командный контакт допустим",
        };
    }

    private function saveJudgeLog(Game $game, JudgeResult $result, int $attempt): void
    {
        $log = new GameJudgeLog();
        $log->setGame($game);
        $log->setScore($result->score);
        $log->setPassed($result->passed);
        $log->setIsSafe($result->isSafe);
        $log->setCriteria($result->criteria);
        $log->setSafetyIssues($result->safetyIssues);
        $log->setFailReason($result->failReason);
        $log->setAttempt($attempt);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
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
        return array_slice($games, 0, 5);
    }

    private function validateAiResponse(array $aiData): void
    {
        if (empty($aiData)) {
            throw new ApiException(ErrorCode::GENERATION_FAILED);
        }

        if (empty($aiData['title']) || !is_string($aiData['title'])) {
            throw new ApiException(ErrorCode::GENERATION_FAILED);
        }

        if (empty($aiData['stages']) || !is_array($aiData['stages']) || count($aiData['stages']) === 0) {
            throw new ApiException(ErrorCode::GENERATION_FAILED);
        }

        $totalDuration = 0;
        foreach ($aiData['stages'] as $index => $stage) {
            if (empty($stage['title']) || !is_string($stage['title'])) {
                throw new ApiException(ErrorCode::GENERATION_FAILED);
            }

            if (empty($stage['description']) || !is_string($stage['description'])) {
                throw new ApiException(ErrorCode::GENERATION_FAILED);
            }

            if (empty($stage['duration']) || !is_numeric($stage['duration']) || $stage['duration'] <= 0) {
                throw new ApiException(ErrorCode::GENERATION_FAILED);
            }

            $totalDuration += (int) $stage['duration'];
        }

        if ($totalDuration === 0) {
            throw new ApiException(ErrorCode::GENERATION_FAILED);
        }
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
        $game->setActivityLevel(GameActivityLevel::from($request->activityLevel));
        $game->setRequisites($request->requisites);
        $game->setIsPublic(false);
        $game->setPhotos($savedPhotos);
        $game->setLocationDescription($request->locationDescription);

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
