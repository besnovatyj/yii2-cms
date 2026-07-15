<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace common\components\log;

use Monolog\Logger;
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
            $this->setLogger($logger);
        }

        parent::init();
    }
}
