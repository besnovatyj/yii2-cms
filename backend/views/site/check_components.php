<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/**
 * Приводит результат любой проверки к единому виду.
 *
 * @param string $status  Статус компонента (Installed / Disabled / Not Installed / Error).
 * @param string $version Версия компонента либо 'N/A'.
 * @param string $note    Дополнительная информация о компоненте.
 * @return array{status: string, version: string, note: string}
 */
function componentResult(string $status, string $version = 'N/A', string $note = ''): array
{
    return ['status' => $status, 'version' => $version, 'note' => $note];
}

/**
 * Безопасно выполняет shell-команду.
 *
 * Не падает, если функция shell_exec отключена (disable_functions) или
 * выполнение завершилось ошибкой — в этом случае возвращает null.
 *
 * @param string $command Команда для выполнения.
 * @return string|null Вывод команды либо null при недоступности/ошибке.
 */
function safeShellExec(string $command): ?string
{
    if (!function_exists('shell_exec')) {
        return null;
    }

    $disabled = array_map('trim', explode(',', (string)ini_get('disable_functions')));
    if (in_array('shell_exec', $disabled, true)) {
        return null;
    }

    try {
        $output = @shell_exec($command);
    } catch (\Throwable $e) {
        return null;
    }

    return is_string($output) ? $output : null;
}

/**
 * Собирает дополнительную информацию по конкретному расширению.
 *
 * @param string $extension Имя расширения.
 * @return string Заметка с деталями либо пустая строка.
 */
function extensionNote(string $extension): string
{
    try {
        switch ($extension) {
            case 'gd':
                if (function_exists('gd_info')) {
                    $info = gd_info();
                    $formats = [];
                    foreach (['JPEG', 'PNG', 'GIF Read', 'WebP', 'AVIF', 'FreeType'] as $key) {
                        if (!empty($info[$key . ' Support']) || !empty($info[$key])) {
                            $formats[] = $key;
                        }
                    }
                    return $formats !== [] ? 'Форматы: ' . implode(', ', $formats) : '';
                }
                return '';

            case 'imagick':
                if (class_exists('Imagick')) {
                    $v = \Imagick::getVersion();
                    return isset($v['versionString']) ? (string)$v['versionString'] : '';
                }
                return '';

            case 'redis':
                return defined('Redis::REDIS_STREAM') ? 'Поддержка streams' : '';

            default:
                return '';
        }
    } catch (\Throwable $e) {
        return '';
    }
}

/**
 * Проверяет, загружено ли PHP-расширение.
 *
 * @param string $extension Имя расширения.
 * @return array{status: string, version: string, note: string}
 */
function checkExtension(string $extension): array
{
    try {
        if (extension_loaded($extension)) {
            $version = phpversion($extension);
            return componentResult(
                'Installed',
                $version !== false && $version !== '' ? $version : 'N/A',
                extensionNote($extension)
            );
        }

        return componentResult('Not Installed');
    } catch (\Throwable $e) {
        return componentResult('Error', $e->getMessage());
    }
}

/**
 * Проверяет наличие системного пакета через apk (Alpine).
 *
 * @param string $package Имя пакета.
 * @return array{status: string, version: string}
 */
function checkSystemPackage(string $package): array
{
    try {
        $output = safeShellExec('apk info -e ' . escapeshellarg($package) . ' 2>/dev/null');
        if ($output === null) {
            return componentResult('Error', 'shell_exec недоступен');
        }

        if (trim($output) !== '') {
            preg_match('/\d+\.\d+\.\d+/', $output, $matches);
            return componentResult('Installed', $matches[0] ?? 'N/A');
        }

        return componentResult('Not Installed');
    } catch (\Throwable $e) {
        return componentResult('Error', $e->getMessage());
    }
}

/**
 * Проверяет наличие бинарного файла в PATH и его версию.
 *
 * @param string $binary Имя бинарного файла.
 * @return array{status: string, version: string, note: string}
 */
function checkBinary(string $binary): array
{
    try {
        $path = safeShellExec('which ' . escapeshellarg($binary) . ' 2>/dev/null');
        if ($path === null) {
            return componentResult('Error', 'shell_exec недоступен');
        }

        $path = trim($path);
        if ($path !== '') {
            $version = trim((string)safeShellExec(escapeshellarg($binary) . ' --version 2>/dev/null'));
            preg_match('/\d+\.\d+\.\d+/', $version, $matches);
            // Первая строка вывода --version как заметка, плюс путь к бинарнику.
            $firstLine = strtok($version, "\n");
            $note = trim(($firstLine !== false ? $firstLine : '') . ' — ' . $path, ' —');
            return componentResult('Installed', $matches[0] ?? 'N/A', $note);
        }

        return componentResult('Not Installed');
    } catch (\Throwable $e) {
        return componentResult('Error', $e->getMessage());
    }
}

/**
 * Проверяет состояние OPcache.
 *
 * Различает три случая: расширение не установлено, установлено но выключено
 * в конфигурации, и полностью включено (со сводкой по памяти и hit-rate).
 *
 * @return array{status: string, version: string, note: string}
 */
function isOpcacheEnabled(): array
{
    try {
        // Расширение вообще не загружено.
        if (!extension_loaded('Zend OPcache') && !function_exists('opcache_get_status')) {
            return componentResult('Not Installed');
        }

        $version = phpversion('Zend OPcache');
        $version = $version !== false && $version !== '' ? $version : 'N/A';

        // Функция статуса может быть недоступна, если opcache.enable=0.
        $status = function_exists('opcache_get_status') ? @opcache_get_status(false) : false;

        if (empty($status) || empty($status['opcache_enabled'])) {
            $configEnabled = filter_var(ini_get('opcache.enable'), FILTER_VALIDATE_BOOL);
            $note = $configEnabled
                ? 'Расширение загружено, но не активно (возможно, отключено для CLI)'
                : 'Расширение установлено, но выключено в конфигурации (opcache.enable=0)';

            return componentResult('Disabled', $version, $note);
        }

        // OPcache активен — соберём краткую сводку.
        $note = '';
        if (isset($status['memory_usage'], $status['opcache_statistics'])) {
            $used = (float)($status['memory_usage']['used_memory'] ?? 0);
            $free = (float)($status['memory_usage']['free_memory'] ?? 0);
            $total = $used + $free;
            $memPart = $total > 0
                ? sprintf('Память: %.1f/%.1f МБ', $used / 1048576, $total / 1048576)
                : '';

            $hits = (float)($status['opcache_statistics']['hits'] ?? 0);
            $misses = (float)($status['opcache_statistics']['misses'] ?? 0);
            $requests = $hits + $misses;
            $ratePart = $requests > 0
                ? sprintf('Hit-rate: %.1f%%', $hits / $requests * 100)
                : '';

            $scripts = (int)($status['opcache_statistics']['num_cached_scripts'] ?? 0);
            $scriptsPart = $scripts > 0 ? sprintf('Скриптов: %d', $scripts) : '';

            $note = implode(', ', array_filter([$memPart, $ratePart, $scriptsPart]));
        }

        return componentResult('Installed', $version, $note);
    } catch (\Throwable $e) {
        return componentResult('Error', $e->getMessage());
    }
}

//echo isOpcacheEnabled() ? 'Enabled' : 'Disabled';

$components = [
    'Системные пакеты' => [
        'supervisor' => checkSystemPackage('supervisor'),
        'libzip' => checkSystemPackage('libzip'),
        'icu-data-full' => checkSystemPackage('icu-data-full'),
        'freetype' => checkSystemPackage('freetype'),
        'libjpeg-turbo' => checkSystemPackage('libjpeg-turbo'),
        'libpng' => checkSystemPackage('libpng'),
        'libwebp' => checkSystemPackage('libwebp'),
        'libavif' => checkSystemPackage('libavif'),
        'zlib' => checkSystemPackage('zlib'),
        'libxml2' => checkSystemPackage('libxml2'),
        'libmemcached' => checkSystemPackage('libmemcached'),
        'imagemagick' => checkSystemPackage('imagemagick'),
        'imagemagick-libs' => checkSystemPackage('imagemagick-libs'),
        'imagemagick-jpeg' => checkSystemPackage('imagemagick-jpeg')
    ],
    'PHP расширения' => [
        'zip' => checkExtension('zip'),
        'intl' => checkExtension('intl'),
        'gd' => checkExtension('gd'),
        'xdebug' => checkExtension('xdebug'),
        'apcu' => checkExtension('apcu'),
        'redis' => checkExtension('redis'),
        'memcached' => checkExtension('memcached'),
        'bcmath' => checkExtension('bcmath'),
        'fileinfo' => checkExtension('fileinfo'),
        'pdo' => checkExtension('pdo'),
        'dom' => checkExtension('dom'),
        'pcntl' => checkExtension('pcntl'),
        'opcache' => isOpcacheEnabled(),
        'pdo_mysql' => checkExtension('pdo_mysql'),
        'calendar' => checkExtension('calendar'),
        'exif' => checkExtension('exif'),
        'imagick' => checkExtension('imagick')
    ],
    'Бинарные файлы' => [
        'composer' => checkBinary('composer'),
        'supervisord' => checkBinary('supervisord')
    ]
];

/**
 * Возвращает CSS-класс строки таблицы по статусу компонента.
 *
 * @param string $status Статус компонента.
 * @return string CSS-класс Bootstrap.
 */
function componentRowClass(string $status): string
{
    return match ($status) {
        'Installed' => 'table-info',
        'Disabled' => 'table-secondary',
        'Error' => 'table-danger',
        default => 'table-warning',
    };
}

/**
 * Человекочитаемая подпись статуса для вывода.
 *
 * @param string $status Внутренний статус компонента.
 * @return string Подпись на русском языке.
 */
function componentStatusLabel(string $status): string
{
    return match ($status) {
        'Installed' => 'Установлено',
        'Disabled' => 'Выключено',
        'Not Installed' => 'Не установлено',
        'Error' => 'Ошибка',
        default => $status,
    };
}

?>

<div class="card">
    <div class="card-header d-md-flex justify-content-md-between">
        <div class="pt-1">Список зависимостей</div>
        <a class="btn btn-sm collapse-button" data-bs-toggle="collapse" href="#check_components" role="button"
           aria-expanded="false" aria-controls="collapseExample">
            <i class="bi bi-plus-lg"></i>
            <i class="bi bi-dash-lg"></i>
        </a>
    </div>
    <div class="collapse" id="check_components">
        <div class="card-body">

            <?php foreach ($components as $category => $items): ?>
                <h5><?php echo htmlspecialchars((string)$category); ?></h5>
                <div class="table-responsive">
                    <table class="table">
                        <tr>
                            <th>Компонент</th>
                            <th>Статус</th>
                            <th>Версия</th>
                            <th>Информация</th>
                        </tr>
                        <?php foreach ($items as $name => $info): ?>
                            <?php
                            $status = (string)($info['status'] ?? 'Error');
                            $version = (string)($info['version'] ?? 'N/A');
                            $note = (string)($info['note'] ?? '');
                            $class = componentRowClass($status);
                            ?>
                            <tr class="<?= $class ?>">
                                <td><?php echo htmlspecialchars((string)$name); ?></td>
                                <td><?php echo htmlspecialchars(componentStatusLabel($status)); ?></td>
                                <td><?php echo htmlspecialchars($version); ?></td>
                                <td class="small text-muted"><?php echo htmlspecialchars($note); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endforeach; ?>

        </div>
    </div>
</div>
