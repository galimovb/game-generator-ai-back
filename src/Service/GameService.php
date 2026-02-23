<?php
// src/Service/GameService.php
namespace App\Service;

use App\DTO\request\GenerateGameRequest;
use App\DTO\request\UpdateGameRequest;
use App\Entity\Game;
use App\Entity\Stage;
use App\Entity\User;
use App\Enum\ErrorCode;
use App\Exception\ApiException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GameService
{
    private const API_URL = 'https://routerai.ru/api/v1';
    private const MODEL = 'qwen/qwen3-vl-8b-thinking';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EntityManagerInterface $entityManager,
        private readonly string $aiApiKey,
    ) {}

    // ==================== ПУБЛИЧНЫЕ МЕТОДЫ ====================

    public function generateAndSave(GenerateGameRequest $request, User $author): Game
    {
        $aiData = $this->callVLM($request);
        return $this->saveGame($aiData, $author, $request);
    }

    public function getPublicGames(int $page, int $limit): array
    {
        $repository = $this->entityManager->getRepository(Game::class);

        $items = $repository->findBy(
            ['isPublic' => true],
            ['createdAt' => 'DESC'],
            $limit,
            ($page - 1) * $limit
        );

        $total = $repository->count(['isPublic' => true]);

        return [
            'items' => $items,
            'total' => $total
        ];
    }

    public function getUserGames(User $user, int $page, int $limit): array
    {
        $repository = $this->entityManager->getRepository(Game::class);

        $items = $repository->findBy(
            ['author' => $user],
            ['createdAt' => 'DESC'],
            $limit,
            ($page - 1) * $limit
        );

        $total = $repository->count(['author' => $user]);

        return [
            'items' => $items,
            'total' => $total
        ];
    }

    public function getGame(int $id, ?User $user = null): Game
    {
        $game = $this->findGameOrFail($id);

        // Проверка доступа к приватной игре
        if (!$game->isPublic()) {
            if (!$user) {
                throw new ApiException(ErrorCode::UNAUTHORIZED);
            }
            if (!$this->isAuthorOrAdmin($game, $user)) {
                throw new ApiException(ErrorCode::FORBIDDEN);
            }
        }

        return $game;
    }

    public function updateGame(int $id, UpdateGameRequest $request, User $user): Game
    {
        $game = $this->findGameOrFail($id);
        $this->checkAccess($game, $user);

        if ($request->title !== null) {
            $game->setTitle($request->title);
        }
        if ($request->description !== null) {
            $game->setDescription($request->description);
        }
        if ($request->minAge !== null) {
            $game->setMinAge($request->minAge);
        }
        if ($request->maxAge !== null) {
            $game->setMaxAge($request->maxAge);
        }
        if ($request->minPlayers !== null) {
            $game->setMinPlayers($request->minPlayers);
        }
        if ($request->maxPlayers !== null) {
            $game->setMaxPlayers($request->maxPlayers);
        }
        if ($request->duration !== null) {
            $game->setDuration($request->duration);
        }
        if ($request->locationType !== null) {
            $game->setLocationType($request->locationType);
        }
        if ($request->requisites !== null) {
            $game->setRequisites($request->requisites);
        }
        if ($request->isPublic !== null) {
            $game->setIsPublic($request->isPublic);
        }

        $this->entityManager->flush();
        $this->entityManager->refresh($game);

        return $game;
    }

    public function deleteGame(int $id, User $user): void
    {
        $game = $this->findGameOrFail($id);
        $this->checkAccess($game, $user);

        $this->entityManager->remove($game);
        $this->entityManager->flush();
    }

    // ==================== PRIVATE МЕТОДЫ ====================

    private function callVLM(GenerateGameRequest $request): array
    {
        $userContent = [
            [
                'type' => 'text',
                'text' => $this->buildPrompt($request)
            ]
        ];

        foreach ($request->photos as $photo) {
            if ($photo instanceof UploadedFile) {
                $userContent[] = [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => $this->convertImageToBase64($photo)
                    ]
                ];
            }
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

    private function convertImageToBase64(UploadedFile $file): string
    {
        $imageData = file_get_contents($file->getPathname());
        $mimeType = $file->getMimeType();
        return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
    }

    private function saveGame(array $aiData, User $author, GenerateGameRequest $request): Game
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
        $game->setLocationType($request->locationType);
        $game->setRequisites($request->requisites);
        $game->setIsPublic(false); // По умолчанию игра приватная

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

            $game->getStages()->add($stage);
            $this->entityManager->persist($stage);
        }

        $this->entityManager->flush();
        $this->entityManager->refresh($game);

        return $game;
    }

    private function findGameOrFail(int $id): Game
    {
        $game = $this->entityManager->getRepository(Game::class)->find($id);
        if (!$game) {
            throw new ApiException(ErrorCode::NOT_FOUND);
        }
        return $game;
    }

    private function checkAccess(Game $game, User $user): void
    {
        if (!$this->isAuthorOrAdmin($game, $user)) {
            throw new ApiException(ErrorCode::FORBIDDEN);
        }
    }

    private function isAuthorOrAdmin(Game $game, User $user): bool
    {
        return $game->getAuthor()->getId() === $user->getId()
            || in_array('ROLE_ADMIN', $user->getRoles());
    }
}