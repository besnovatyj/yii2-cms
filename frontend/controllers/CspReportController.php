<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Request;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use yii\web\TooManyRequestsHttpException;
use Throwable;

/**
 * Контроллер для приёма CSP (Content-Security-Policy) violation reports.
 *
 * Браузер автоматически шлёт POST-запросы с JSON-телом при нарушениях CSP.
 * Этот контроллер принимает, валидирует, фильтрует шум и логирует репорты.
 *
 * Использование:
 * 1. Добавить роут: 'POST csp-report' => 'csp-report/index'
 * 2. Добавить в CSP заголовок: report-uri /csp-report
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP
 * @see https://w3c.github.io/webappsec-csp/#violation-reports
 */
class CspReportController extends Controller
{
    /**
     * Максимальный размер тела запроса в байтах.
     * Защита от flood-атак большими payload'ами.
     */
    private const int|float MAX_PAYLOAD_SIZE = 10 * 1024; // 10 KB

    /**
     * Максимум репортов с одного IP в минуту.
     */
    private const int RATE_LIMIT_PER_MINUTE = 60;

    /**
     * Время хранения счётчика rate limit в секундах.
     */
    private const int RATE_LIMIT_TTL = 60;

    /**
     * Отключаем CSRF — браузер не присылает токен с CSP-репортами.
     */
    public $enableCsrfValidation = false;

    /**
     * Паттерны источников, которые считаются шумом (расширения браузера и т.п.).
     */
    private const array NOISE_PATTERNS = [
        'chrome-extension://',
        'moz-extension://',
        'safari-extension://',
        'safari-web-extension://',
        'ms-browser-extension://',
        'about:',
        'blob:',
        'data:',
        // Частые false-positives
        'googletagmanager.com',
        'google-analytics.com',
        'mc.yandex.ru',
        'metrika.yandex',
    ];

    /**
     * Допустимые Content-Type для CSP-репортов.
     */
    private const array ALLOWED_CONTENT_TYPES = [
        'application/csp-report',
        'application/json',
    ];

    /**
     * @throws BadRequestHttpException
     */
    public function beforeAction($action): bool
    {
        // Разрешаем cross-origin для CSP репортов
        $response = Yii::$app->response;
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'POST');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');

        return parent::beforeAction($action);
    }

    /**
     * Принимает CSP violation report от браузера.
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws TooManyRequestsHttpException
     */
    public function actionIndex(): Response
    {
        $request = Yii::$app->request;

        // Валидация запроса
        $this->validateRequest($request);

        // Rate limiting
        $this->checkRateLimit($request->userIP);

        // Парсинг и валидация payload
        $report = $this->parseReport($request->rawBody);

        // Фильтрация шума
        if ($this->isNoise($report)) {
            return $this->asJson([
                'status' => 'ignored',
                'reason' => 'noise',
            ]);
        }

        // Логирование
        $this->logReport($report, $request->userIP);

        Yii::$app->response->statusCode = 204; // No Content — стандартный ответ
        return $this->asJson(['status' => 'ok']);
    }

    /**
     * Валидирует HTTP-запрос.
     *
     * @param Request $request
     * @throws BadRequestHttpException
     */
    private function validateRequest(Request $request): void
    {
        // Только POST
        if (!$request->isPost) {
            throw new BadRequestHttpException('Only POST method is allowed');
        }

        // Проверяем Content-Type
        $contentType = $request->headers->get('Content-Type', '');
        $isValidContentType = false;

        foreach (self::ALLOWED_CONTENT_TYPES as $allowed) {
            if (stripos($contentType, $allowed) !== false) {
                $isValidContentType = true;
                break;
            }
        }

        if (!$isValidContentType) {
            throw new BadRequestHttpException('Invalid Content-Type header');
        }

        // Проверяем размер payload
        if (strlen($request->rawBody) > self::MAX_PAYLOAD_SIZE) {
            throw new BadRequestHttpException('Payload too large');
        }
    }

    /**
     * Rate limiting по IP через кеш.
     *
     * @param string|null $ip
     * @throws TooManyRequestsHttpException
     */
    private function checkRateLimit(?string $ip): void
    {
        if ($ip === null) {
            return; // Не можем проверить — пропускаем
        }

        $cache = Yii::$app->cache;
        if ($cache === null) {
            return; // Кеш не настроен — пропускаем rate limit
        }

        $key = sprintf('csp_rate_limit_%s', md5($ip));
        $count = $cache->get($key);

        if ($count === false) {
            $cache->set($key, 1, self::RATE_LIMIT_TTL);
            return;
        }

        if ($count >= self::RATE_LIMIT_PER_MINUTE) {
            throw new TooManyRequestsHttpException(
                'Too many CSP reports. Please try again later.'
            );
        }

        $cache->set($key, $count + 1, self::RATE_LIMIT_TTL);
    }

    /**
     * Парсит JSON-тело запроса и извлекает CSP-репорт.
     *
     * @param string $rawBody
     * @return array
     * @throws BadRequestHttpException
     */
    private function parseReport(string $rawBody): array
    {
        if (empty($rawBody)) {
            throw new BadRequestHttpException('Empty request body');
        }

        $data = json_decode($rawBody, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new BadRequestHttpException(
                'Invalid JSON: ' . json_last_error_msg()
            );
        }

        // CSP Level 2 format: {"csp-report": {...}}
        if (isset($data['csp-report']) && is_array($data['csp-report'])) {
            return $data['csp-report'];
        }

        // CSP Level 3 format (Reporting API): может быть массив репортов
        if (isset($data[0]['body']) && is_array($data[0]['body'])) {
            return $data[0]['body'];
        }

        throw new BadRequestHttpException('Invalid CSP report format');
    }

    /**
     * Проверяет, является ли репорт "шумом" (расширения браузера и т.п.).
     *
     * @param array $report
     * @return bool
     */
    private function isNoise(array $report): bool
    {
        $sourceFile = $report['source-file'] ?? $report['sourceFile'] ?? '';
        $blockedUri = $report['blocked-uri'] ?? $report['blockedURL'] ?? '';
        $documentUri = $report['document-uri'] ?? $report['documentURL'] ?? '';

        $checkFields = [$sourceFile, $blockedUri];

        foreach ($checkFields as $field) {
            foreach (self::NOISE_PATTERNS as $pattern) {
                if (stripos($field, $pattern) !== false) {
                    return true;
                }
            }
        }

        // Игнорируем репорты не с нашего домена (возможно, спам)
        if (!empty($documentUri)) {
            $ourHosts = $this->getOurHosts();
            $reportHost = parse_url($documentUri, PHP_URL_HOST);

            if ($reportHost && !in_array($reportHost, $ourHosts, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Возвращает список наших доменов для фильтрации.
     *
     * @return array
     */
    private function getOurHosts(): array
    {
        // Получаем из переменных окружения или конфига
        $hosts = [
            Yii::$app->request->hostName,
        ];

        // Добавляем из env если есть
        $envHosts = [
            getenv('MAIN_HOST'),
            getenv('ADM_HOST'),
            getenv('FILES_HOST'),
        ];

        foreach ($envHosts as $host) {
            if ($host !== false && $host !== '') {
                $hosts[] = $host;
                $hosts[] = 'www.' . $host;
            }
        }

        return array_filter(array_unique($hosts));
    }

    /**
     * Логирует CSP violation report.
     *
     * @param array $report
     * @param string|null $ip
     */
    private function logReport(array $report, ?string $ip): void
    {
        $logEntry = [
            'timestamp' => date('c'), // ISO 8601
            'ip' => $this->anonymizeIp($ip),
            'document_uri' => $this->truncate($report['document-uri'] ?? $report['documentURL'] ?? null, 500),
            'referrer' => $this->truncate($report['referrer'] ?? null, 500),
            'blocked_uri' => $this->truncate($report['blocked-uri'] ?? $report['blockedURL'] ?? null, 500),
            'violated_directive' => $report['violated-directive'] ?? $report['violatedDirective'] ?? null,
            'effective_directive' => $report['effective-directive'] ?? $report['effectiveDirective'] ?? null,
            'original_policy' => $this->truncate($report['original-policy'] ?? $report['originalPolicy'] ?? null, 1000),
            'source_file' => $this->truncate($report['source-file'] ?? $report['sourceFile'] ?? null, 500),
            'line_number' => $report['line-number'] ?? $report['lineNumber'] ?? null,
            'column_number' => $report['column-number'] ?? $report['columnNumber'] ?? null,
            'status_code' => $report['status-code'] ?? $report['statusCode'] ?? null,
        ];

        // Убираем null значения для компактности
        $logEntry = array_filter($logEntry, static fn($v) => $v !== null);

        try {
            $this->writeToFile($logEntry);
        } catch (Throwable $e) {
            // Fallback на Yii logger если файл недоступен
            Yii::warning([
                'message' => 'CSP Violation (file write failed)',
                'report' => $logEntry,
                'error' => $e->getMessage(),
            ], 'security.csp');
        }

        // Дополнительно в Yii logger для критичных нарушений
        if ($this->isCriticalViolation($report)) {
            Yii::warning([
                'message' => 'Critical CSP Violation',
                'report' => $logEntry,
            ], 'security.csp');
        }
    }

    /**
     * Записывает лог в файл.
     *
     * @param array $logEntry
     */
    private function writeToFile(array $logEntry): void
    {
        $logDir = Yii::getAlias('@runtime/logs');
        $logFile = $logDir . '/csp-reports.log';

        // Убеждаемся что директория существует
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $line = json_encode(
            $logEntry,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ) . "\n";

        file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Определяет, является ли нарушение критичным.
     *
     * @param array $report
     * @return bool
     */
    private function isCriticalViolation(array $report): bool
    {
        $directive = $report['violated-directive']
            ?? $report['effective-directive']
            ?? $report['violatedDirective']
            ?? '';

        // Критичные директивы — возможные XSS или инъекции
        $criticalDirectives = [
            'script-src',
            'object-src',
            'base-uri',
            'form-action',
        ];

        // foreach ($criticalDirectives as $critical) { if (stripos($directive, $critical) !== false) { return true; } }
        return array_any($criticalDirectives, fn($critical) => stripos($directive, $critical) !== false);

    }

    /**
     * Анонимизирует IP (GDPR compliance).
     * Зануляет последний октет для IPv4, последние 80 бит для IPv6.
     *
     * @param string|null $ip
     * @return string|null
     */
    private function anonymizeIp(?string $ip): ?string
    {
        if ($ip === null) {
            return null;
        }

        // IPv4
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return preg_replace('/\.\d+$/', '.0', $ip);
        }

        // IPv6
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return preg_replace('/:[^:]+:[^:]+:[^:]+:[^:]+:[^:]+$/', ':0:0:0:0:0', $ip);
        }

        return null;
    }

    /**
     * Обрезает строку до максимальной длины.
     *
     * @param string|null $value
     * @param int $maxLength
     * @return string|null
     */
    private function truncate(?string $value, int $maxLength): ?string
    {
        if ($value === null) {
            return null;
        }

        if (strlen($value) <= $maxLength) {
            return $value;
        }

        return substr($value, 0, $maxLength - 3) . '...';
    }
}
