<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

// @see https://github.com/samdark/yii2-psr-log-target
// @see https://habr.com/ru/articles/323584/
// @see https://github.com/Seldaek/monolog

use common\components\log\MonologTarget;

/**
 * Единая декларативная конфигурация каналов логирования.
 *
 * Канал — это таргет {@see MonologTarget}, описанный чистым массивом скаляров.
 * Сам логгер Monolog с хендлерами собирается внутри таргета (MonologTarget::init),
 * здесь — только декларация: какие категории/уровни → в какой канал → каким
 * целям (файл, syslog, ...).
 *
 * Разделение ответственности:
 * - МАРШРУТ задаётся `categories`/`except`/`levels` (штатный yii\log\Dispatcher);
 * - ПРИЁМНИК задаётся `handlers` (Monolog-хендлеры). Один канал может писать
 *   сразу в несколько целей (например, auth → файл + syslog).
 *
 * Где объявляются каналы:
 * - core-каналы (app, auth) — здесь, ниже;
 * - каналы модулей — модуль отдаёт getLogChannels(), modman при установке
 *   складывает их в @config-dyn-gen/logChannelsConfigFile.php, и они
 *   подмешиваются в targets ниже (мёрж по id канала).
 *
 * Конвенция имён файлов (совместимость с модулем-вьювером
 * besnovatyj/yii2-cms-monolog, регэксп /monolog.*\.log$/):
 *   глобальный  → monolog.log
 *   канал {x}   → monolog-{x}.log
 *
 * JSON-формат файлового хендлера (одна запись на строку, BATCH_MODE_NEWLINES),
 * который ожидает модуль-вьювер:
 * ```json
 * {
 * "message": "Текст сообщения",
 * "context": {
 * "timestamp": 1737203456.789,
 * "userId": 123,
 * "custom_data": "..."
 * },
 * "level": 200,
 * "level_name": "INFO",
 * "channel": "yii2-cms",
 * "datetime": "2026-01-18T10:30:56.789012+00:00",
 * "extra": {
 * "file": "/path/to/file.php",
 * "line": 42,
 * "class": "app\\controllers\\SiteController",
 * "function": "actionIndex"
 * }
 * }
 * ```
 *
 * Преимущества этого подхода:
 * 1. BATCH_MODE_NEWLINES - каждая запись на отдельной строке, легко читать файл построчно
 * 2. includeStacktraces: true - для ошибок будет полный стектрейс
 * 3. ignoreEmptyContextAndExtra: true - не захламляет JSON пустыми полями
 * 4. Timestamp в context - благодаря addTimestampToContext: true в PsrTarget
 */

// Каналы устанавливаемых модулей, собранные modman при их установке.
// Файл генерируется автоматически; пока ни один модуль со своим каналом
// не установлен, его может не быть — деградируем до пустого набора, а не в фатал.
$moduleChannelsFile = Yii::getAlias('@config-dyn-gen/logChannelsConfigFile.php');
$moduleChannels = is_file($moduleChannelsFile) ? require $moduleChannelsFile : [];
if (!is_array($moduleChannels)) {
    $moduleChannels = [];
}

// modman — core-модуль (EDITABLE=false, всегда установлен, не проходит через
// install-сборщик каналов). Его канал берём напрямую из единого источника,
// чтобы не дублировать спек. Generic-сборщик (logChannelsConfigFile) остаётся
// для будущих УСТАНАВЛИВАЕМЫХ модулей.
$modmanChannel = \Besnovatyj\Modman\Module::logChannels();

$coreTargets = [
    // Глобальный лог приложения (catch-all). Категории выделенных каналов
    // (modman/*, auth/*) исключены, чтобы не засорять общий файл.
    // Файл: @runtime/logs/monolog-Y-m-d.log
    //$psrLogger->pushHandler(new \Monolog\Handler\SlackHandler('slack_token', 'logs', null, true, null, \Monolog\Level::Debug));
    'global' => [ // @see https://github.com/samdark/yii2-psr-log-target
        'class' => MonologTarget::class,
        'channel' => 'yii2-cms',
        // 404 выделены в свой канал (см. 'notfound' ниже) — из общего файла исключаем.
        'except' => ['modman/*', 'auth/*', 'yii\web\HttpException:404'],
        // Уровень-порог хендлера: встроенные хендлеры Monolog используют
        // минимальный порог уровня логирования.
        'handlers' => [
            ['type' => 'rotating_file', 'file' => '@runtime/logs/monolog.log', 'maxFiles' => 30, 'level' => 'notice'],
        ],

        // Обогащение записей полями текущего HTTP-запроса (уходят в `extra`
        // каждой строки, рендерятся блоком "Extra" во вьювере yii2-cms-monolog).
        // Ключ — имя поля в extra, значение — ключ в $_SERVER. Отсутствующие
        // ключи попадают как null (SSL_CIPHER на plain HTTP, REDIRECT_STATUS
        // вне php-fpm и т.п.). IP тут = REMOTE_ADDR (прямой клиент, без прокси);
        // при появлении прокси/CDN брать HTTP_X_FORWARDED_FOR + trustedHosts.
        'webProcessorFields' => [
            'ip'              => 'REMOTE_ADDR',
            'http_method'     => 'REQUEST_METHOD',
            'url'             => 'REQUEST_URI',
            'query_string'    => 'QUERY_STRING',
            'scheme'          => 'REQUEST_SCHEME',
            'referrer'        => 'HTTP_REFERER',
            'user_agent'      => 'HTTP_USER_AGENT',
            'ssl_cipher'      => 'SSL_CIPHER',
            'redirect_status' => 'REDIRECT_STATUS',
            'remote_port'     => 'REMOTE_PORT',
            'request_time'    => 'REQUEST_TIME',
        ],

        // It is optional parameter. The message levels that this target is interested in.
        // The parameter can be an array.
        //'levels' => ['info', yii\log\Logger::LEVEL_WARNING, Psr\Log\LogLevel::CRITICAL],

        // It is optional parameter. Default value is false. If you use Yii log buffering, you see buffer write time, and not real timestamp.
        // If you want to write real time to logs, you can set addTimestampToContext as true and use timestamp from log event context.
        'addTimestampToContext' => true,
        'extractExceptionTrace' => true,
    ],

    // Канал аутентификации. Уходит в syslog (facility LOG_AUTH) для fail2ban
    // и в отдельный файл для просмотра в админке. В общий monolog.log НЕ течёт
    // (исключён через except у samdark-monolog).
    'auth' => [
        'class' => MonologTarget::class,
        'channel' => 'auth',
        'categories' => ['auth/*'],
        // level 'info': в Yii нет 'notice'; успешный вход — Yii::info (INFO),
        // неуспешный — Yii::warning (WARNING). Порог 'info' пропускает оба.
        'handlers' => [
            ['type' => 'rotating_file', 'file' => '@runtime/logs/monolog-auth.log', 'maxFiles' => 30, 'level' => 'info'],
            ['type' => 'syslog', 'ident' => 'yii2-auth', 'facility' => 'LOG_AUTH', 'level' => 'info'],
        ],
        'addTimestampToContext' => true,
        'extractExceptionTrace' => true,
    ],

    // Канал «страница не найдена». Yii\web\ErrorHandler логирует
    // NotFoundHttpException под категорией `yii\web\HttpException:404`
    // уровнем ERROR. Ловим ровно эту категорию в отдельный файл, чтобы
    // 404 (боты, битые ссылки, сканеры) не засоряли общий monolog.log.
    // Файл: @runtime/logs/monolog-notfound-Y-m-d.log
    // Ротация посуточная; RotatingFileHandler сам удаляет самые старые
    // файлы, когда их накапливается больше maxFiles.
    'notfound' => [
        'class' => MonologTarget::class,
        'channel' => 'notfound',
        'categories' => ['yii\web\HttpException:404'],
        'levels' => ['error'],
        'handlers' => [
            // includeStacktraces => false — не разворачивать стек в context.exception.
            ['type' => 'rotating_file', 'file' => '@runtime/logs/monolog-notfound.log', 'maxFiles' => 14, 'level' => 'error', 'includeStacktraces' => false],
        ],
        // Для 404 важнее всего: какой URL, кто (IP/UA) и откуда (referrer).
        'webProcessorFields' => [
            'ip'           => 'REMOTE_ADDR',
            'http_method'  => 'REQUEST_METHOD',
            'url'          => 'REQUEST_URI',
            'query_string' => 'QUERY_STRING',
            'referrer'     => 'HTTP_REFERER',
            'user_agent'   => 'HTTP_USER_AGENT',
        ],
        'addTimestampToContext' => true,
        // extractExceptionTrace оставляем true, чтобы message был коротким
        // («Страница не найдена»), а не полным __toString() со стеком;
        // сам массив кадров стека убираем через dropTraceContext.
        'extractExceptionTrace' => true,
        'dropTraceContext' => true,
    ],

];

return [
    'log' => [
        // Мёрдж по id канала: core-каналы + modman + каналы установленных модулей.
        'targets' => array_merge($coreTargets, $modmanChannel, $moduleChannels),
    ],
];


