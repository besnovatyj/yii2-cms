<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace common\components\log;

use Monolog\Logger;
use Monolog\LogRecord;
use Monolog\Processor\WebProcessor;
use samdark\log\PsrTarget;

/**
 * Самособирающийся Yii-таргет логирования поверх Monolog.
 *
 * Принимает декларативный спек канала (только скаляры/массивы — полностью
 * сериализуем сборщиком конфигов modman, без замыканий и объектов) и сам
 * строит {@see Logger} с нужными хендлерами в {@see self::init()}.
 *
 * Маршрутизация (какие категории/уровни попадают в канал) задаётся штатными
 * свойствами `categories`/`except`/`levels` базового `yii\log\Target` — здесь
 * ничего не переопределяется, только конфигурируется.
 *
 * Пример конфигурации одного канала:
 * ```php
 * [
 *     'class'      => \common\components\log\MonologTarget::class,
 *     'channel'    => 'auth',
 *     'categories' => ['auth/*'],
 *     'handlers'   => [
 *         ['type' => 'rotating_file', 'file' => '@runtime/logs/monolog-auth.log'],
 *         ['type' => 'syslog', 'ident' => 'yii2-auth', 'facility' => 'LOG_AUTH', 'level' => 'notice'],
 *     ],
 *     'addTimestampToContext' => true,
 *     'extractExceptionTrace' => true,
 * ]
 * ```
 */
class MonologTarget extends PsrTarget
{
    /**
     * Имя Monolog-канала. Также используется как основа имени лог-файла
     * по конвенции `monolog-{channel}.log` (совместимость с модулем-вьювером).
     */
    public string $channel = 'app';

    /**
     * Список спеков хендлеров канала. Каждый элемент обрабатывается
     * {@see HandlerFactory::create()}.
     *
     * @var array<int, array>
     */
    public array $handlers = [];

    /**
     * По умолчанию не пишем глобальные переменные ($_GET, $_POST и т.д.) —
     * каналу обычно нужны только сообщения. Переопределяется конфигом.
     *
     * @var array
     */
    public $logVars = [];

    /**
     * Карта полей `$_SERVER`, добавляемых в `extra` КАЖДОЙ записи канала через
     * {@see WebProcessor}: ключ — имя поля в `extra`, значение — имя ключа в
     * `$_SERVER`. Пустой массив (по умолчанию) — процессор не подключается,
     * поэтому служебные каналы (напр. `auth` → syslog для fail2ban) остаются
     * с «чистой» строкой без лишних полей.
     *
     * Отсутствующие в `$_SERVER` ключи попадают в `extra` как `null`
     * (например, SSL_CIPHER на plain HTTP или REDIRECT_STATUS вне php-fpm).
     *
     * @var array<string, string>
     */
    public array $webProcessorFields = [];

    /**
     * Убирать ли из каждой записи массив `context.trace`, который базовый
     * {@see PsrTarget} добавляет при `extractExceptionTrace = true`.
     *
     * Нужно для «шумных» каналов (например, 404): у них имеет смысл оставить
     * короткое сообщение исключения, но не тащить в файл десятки кадров стека.
     * Работает в паре с `includeStacktraces => false` у файлового хендлера
     * (тот отвечает за трейс внутри `context.exception`).
     */
    public bool $dropTraceContext = false;

    /**
     * Собирает Monolog-логгер из спека до инициализации базового таргета.
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function init(): void
    {
        if ($this->logger === null) {
            $logger = new Logger($this->channel);
            foreach ($this->handlers as $spec) {
                $logger->pushHandler(HandlerFactory::create($spec));
            }
            // Обогащаем каждую запись полями текущего HTTP-запроса (IP, URL и т.д.).
            // WebProcessor сам no-op'ит в CLI (нет $_SERVER['REQUEST_URI']), поэтому
            // отдельная проверка SAPI не нужна.
            if ($this->webProcessorFields !== []) {
                $logger->pushProcessor(new WebProcessor(null, $this->webProcessorFields));
            }
            // Вырезаем массив кадров стека из context (см. $dropTraceContext).
            if ($this->dropTraceContext) {
                $logger->pushProcessor(static function (LogRecord $record): LogRecord {
                    if (!isset($record->context['trace'])) {
                        return $record;
                    }
                    $context = $record->context;
                    unset($context['trace']);

                    return $record->with(context: $context);
                });
            }
            $this->setLogger($logger);
        }

        parent::init();
    }
}
