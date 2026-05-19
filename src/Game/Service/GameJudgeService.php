<?php

namespace App\Game\Service;

use App\Game\DTO\JudgeResult;
use App\Game\DTO\Request\GenerateGameRequest;
use App\Shared\Enum\GameActivityLevel;
use App\Shared\Enum\ModelType;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GameJudgeService
{
    private const PASS_THRESHOLD  = 0.65;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $aiApiKey,
        private readonly string $aiApiUrl,
        private readonly LoggerInterface $logger,
        private  GameGenerationService $gameGenerationService,
    ) {}

    public function evaluate(array $aiData, GenerateGameRequest $request, array $photos = []): JudgeResult
    {
        $prompt = $this->buildJudgePrompt($aiData, $request);

        $userContent = [['type' => 'text', 'text' => $prompt]];
        foreach ($photos as $photoBase64) {
            $userContent[] = ['type' => 'image_url', 'image_url' => ['url' => $photoBase64]];
        }

        try {
            $response = $this->httpClient->request('POST', $this->aiApiUrl . '/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->aiApiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model'           => ModelType::validationModel(),
                    'messages'        => [
                        ['role' => 'system', 'content' => 'Ты эксперт по детским играм и безопасности. Оценивай объективно.' . (!empty($photos) ? ' Проанализируй фото площадки — убедись что игра соответствует реальному пространству.' : '') . ' Отвечай только в JSON.'],
                        ['role' => 'user', 'content' => $userContent],
                    ],
                    'temperature'     => 0.1,
                    'response_format' => ['type' => 'json_object'],
                    'enable_thinking' => false,
                ],
                'timeout' => 180
            ]);

            $data    = $response->toArray();
            $content = $data['choices'][0]['message']['content'] ?? '{}';
            $result  = json_decode($content, true);

            $this->logger->debug('Raw judge response', [
                'content' => $content,           // сырой JSON от судьи
                'result'  => $result,            // распарсенный массив
            ]);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($result)) {
                $this->logger->warning('Judge returned invalid JSON, treating as passed');
                return $this->passThroughResult();
            }

            $judgeResult = $this->parseResult($result);

            $this->logger->info('Judge evaluation', [
                'score'        => $judgeResult->score,
                'passed'       => $judgeResult->passed,
                'is_safe'      => $judgeResult->isSafe,
                'safety_issues' => $judgeResult->safetyIssues,
                'fail_reason'  => $judgeResult->failReason,
                'game_title'   => $aiData['title'] ?? '?',
            ]);

            return $judgeResult;

        } catch (\Throwable $e) {
            // Если судья недоступен — пропускаем, не блокируем генерацию
            $this->logger->error('Judge request failed, skipping evaluation', [
                'error' => $e->getMessage(),
            ]);
            return $this->passThroughResult();
        }
    }

    private function buildJudgePrompt(array $aiData, GenerateGameRequest $request): string
    {
        $gameJson      = json_encode($aiData, JSON_UNESCAPED_UNICODE);
        $totalDuration = array_sum(array_column($aiData['stages'] ?? [], 'duration'));
        $passThreshold = self::PASS_THRESHOLD;
        $ageLimit      = $this->gameGenerationService->getAgeSafetyConstraints($request->age);

        $activityLabel = GameActivityLevel::from($request->activityLevel)->getLabel();

        return <<<PROMPT
Оцени детскую игру. Возраст: {$request->age} лет, игроков: {$request->players}, длительность: {$request->duration} мин, активность: {$activityLabel}, площадка: {$request->fieldWidth}x{$request->fieldLength} м.

Физические ограничения для {$request->age} лет: {$ageLimit}

ИГРА: {$gameJson}

Фактическая длительность этапов: {$totalDuration} мин.

Оцени от 0.0 до 1.0:
БЕЗОПАСНОСТЬ:
- safety_physical (физическая нагрузка соответствует возрасту {$request->age} лет и ограничениям выше)
- safety_content (нет буллинга, унижений, жестокости, взрослых тем)
- safety_props (реквизит безопасен для детей: нет острых предметов, тяжёлых снарядов, опасных конструкций)
- is_safe = true только если все три параметра приемлемы
- safety_issues — список конкретных проблем

КАЧЕСТВО:
- duration_accuracy (сумма этапов {$totalDuration} мин vs запрос {$request->duration} мин: отклонение ≤2 мин = 1.0, ≤5 мин = 0.5, иначе 0.0)
- age_appropriateness (правила, сложность и механики понятны и интересны детям {$request->age} лет)
- player_coverage (у каждого из {$request->players} игроков есть активная роль, никто не стоит в стороне)
- activity_match (уровень физической активности игры соответствует запросу: {$activityLabel})
- description_clarity (правила и этапы описаны чётко, ведущий сможет провести игру без вопросов)
- stage_variety (этапы разнообразны по механике, не повторяют друг друга)

JSON:
{"safety":{"is_safe":true,"safety_physical":0.0,"safety_content":0.0,"safety_props":0.0,"safety_issues":[]},"quality":{"duration_accuracy":0.0,"age_appropriateness":0.0,"player_coverage":0.0,"activity_match":0.0,"description_clarity":0.0,"stage_variety":0.0},"total_score":0.0,"passed":true,"fail_reason":null}

total_score = среднее quality. passed = is_safe=true И total_score>={$passThreshold}. ВЕРНИ ТОЛЬКО JSON.
PROMPT;
    }

    private function parseResult(array $raw): JudgeResult
    {
        $safety  = $raw['safety']  ?? [];
        $quality = $raw['quality'] ?? [];

        $safetyIssues = (array) ($safety['safety_issues'] ?? []);
        $isSafe       = (bool)  ($safety['is_safe'] ?? true);
        $score        = (float) ($raw['total_score'] ?? (count($quality) > 0 ? array_sum($quality) / count($quality) : 0.0));
        $passed       = $isSafe && (bool) ($raw['passed'] ?? ($score >= self::PASS_THRESHOLD));

        return new JudgeResult(
            score:        $score,
            passed:       $passed,
            isSafe:       $isSafe,
            criteria:     $quality,
            safetyIssues: $safetyIssues,
            failReason:   $raw['fail_reason'] ?? null,
        );
    }

    private function passThroughResult(): JudgeResult
    {
        return new JudgeResult(
            score:        1.0,
            passed:       true,
            isSafe:       true,
            criteria:     [],
            safetyIssues: [],
            failReason:   null,
        );
    }
}
