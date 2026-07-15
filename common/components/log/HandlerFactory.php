<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace common\components\log;

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Level;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Фабрика Monolog-хендлеров по декларативному спеку канала.
 *
 * Спек — массив скаляров (полностью `var_export`-абелен сборщиком конфигов modman),
 * никаких объектов и замыканий. Новый тип цели логирования добавляется одним
 * `case` в {@see self::create()} — вызовы `Yii::info()` и модуль-вьювер при этом
 * не затрагиваются.
 *
 * Поддерживаемые типы:
 * Уровень `level` по умолчанию — `debug` (fail-open): канал без явного порога
 * пишет всё, а не молча теряет `info`/`debug`. Ограничение задаётся явно в
 * спеке канала (см. `app/common/config/log.php`).
 *
 * - `rotating_file` — ротируемый файл в JSON-формате (совместим с модулем
 *   просмотра `besnovatyj/yii2-cms-monolog`). Параметры: `file` (алиас/путь),
 *   `maxFiles` (по умолчанию 30), `level` (по умолчанию `debug`).
 * - `syslog` — отправка в syslog (используется для fail2ban по auth-событиям).
 *   Параметры: `ident`, `facility` (имя константы, по умолчанию `LOG_USER`),
 *   `level` (по умолчанию `debug`).
 */
final class HandlerFactory
{
    /**
     * Карта строковых имён facility syslog в PHP-константы.
     * В декларативном спеке нельзя хранить константу, поэтому маппим по имени.
     */
    private const FACILITY_MAP = [
        'LOG_AUTH' => LOG_AUTH,
        'LOG_AUTHPRIV' => LOG_AUTHPRIV,
        'LOG_USER' => LOG_USER,
        'LOG_DAEMON' => LOG_DAEMON,
        'LOG_LOCAL0' => LOG_LOCAL0,
        'LOG_LOCAL1' => LOG_LOCAL1,
        'LOG_LOCAL2' => LOG_LOCAL2,
        'LOG_LOCAL3' => LOG_LOCAL3,
        'LOG_LOCAL4' => LOG_LOCAL4,
        'LOG_LOCAL5' => LOG_LOCAL5,
        'LOG_LOCAL6' => LOG_LOCAL6,
        'LOG_LOCAL7' => LOG_LOCAL7,
    ];

    /**
     * Создаёт Monolog-хендлер по спеку.
     *
     * @param array $spec Спек одного хендлера канала (см. описание класса).
     * @throws InvalidConfigException При неизвестном типе или нехватке параметров.
     */
    public static function create(array $spec): HandlerInterface
    {
        $type = $spec['type'] ?? null;

        return match ($type) {
            'rotating_file' => self::rotatingFile($spec),
            'syslog' => self::syslog($spec),
            default => throw new InvalidConfigException(
                "Unknown log handler type: " . var_export($type, true)
            ),
        };
    }

    /**
     * Ротируемый файловый хендлер с JSON-форматтером.
     *
     * JSON-формат намеренно совпадает с тем, что ожидает модуль просмотра
     * `besnovatyj/yii2-cms-monolog` (одна JSON-запись на строку):
     * - BATCH_MODE_NEWLINES — каждая запись на отдельной строке;
     * - appendNewline = true;
     * - ignoreEmptyContextAndExtra = true — не захламлять JSON пустыми полями;
     * - includeStacktraces = true — полный стектрейс для исключений.
     *
     * @throws InvalidConfigException
     */
    private static function rotatingFile(array $spec): RotatingFileHandler
    {
        if (empty($spec['file'])) {
            throw new InvalidConfigException("Log handler 'rotating_file' requires 'file'.");
        }

        $handler = new RotatingFileHandler(
            Yii::getAlias($spec['file']),
            (int)($spec['maxFiles'] ?? 30),
            self::level($spec['level'] ?? 'debug'),
        );
        $handler->setFormatter(new JsonFormatter(
            JsonFormatter::BATCH_MODE_NEWLINES,
            true,
            true,
            true,
        ));

        return $handler;
    }

    /**
     * Syslog-хендлер (цель для fail2ban по событиям аутентификации).
     *
     * @throws InvalidConfigException
     */
    private static function syslog(array $spec): SyslogHandler
    {
        if (empty($spec['ident'])) {
            throw new InvalidConfigException("Log handler 'syslog' requires 'ident'.");
        }

        $facilityName = $spec['facility'] ?? 'LOG_USER';
        if (!isset(self::FACILITY_MAP[$facilityName])) {
            throw new InvalidConfigException("Unknown syslog facility: $facilityName");
        }

        return new SyslogHandler(
            $spec['ident'],
            self::FACILITY_MAP[$facilityName],
            self::level($spec['level'] ?? 'debug'),
        );
    }

    /**
     * Преобразует строковое имя уровня (`notice`, `error`, ...) в {@see Level}.
     *
     * @throws InvalidConfigException
     */
    private static function level(string $name): Level
    {
        try {
            return Level::fromName(ucfirst(strtolower($name)));
        } catch (\Throwable) {
            throw new InvalidConfigException("Unknown log level: $name");
        }
    }
}
