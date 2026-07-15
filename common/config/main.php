<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/**
 * Массив компонентов. После миграции на yiisoft/config ядро НЕ потребляет генерируемые modman артефакты:
 *  - модули приходят через config-plugin (merge-plan), системный Modman — через свой Bootstrap;
 *  - компоненты/bootstrap модулей — тоже через config-plugin;
 *  - лог-канал modman — через Bootstrap (см. common/config/log.php).
 * Поэтому здесь только статические компоненты ядра. См. /TODO_YII3_CONFIG.MD.
 */
$components = require __DIR__ . '/components.php';

// Компонент лога.
if (is_array($log = require __DIR__ . '/log.php')) {
    $components = array_merge_recursive($components, $log);
}

return [
    'language' => 'ru-RU',
    'timeZone' => getenv('TIME_ZONE'),
    'bootstrap' => [
        'log',
        'queue',
        \Besnovatyj\Modman\Bootstrap::class,
        // bootstrap-классы модулей приходят через config-plugin (merge-plan), а не из артефакта.
    ],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'runtimePath' => '@runtime',
    'vendorPath' => dirname(__DIR__, 2) . '/vendor',
    'components' => $components,
    // 'modules' задаётся модулями через config-plugin; системный Modman регистрирует себя в Bootstrap.
    'container' => require __DIR__ . '/container.php',
];
