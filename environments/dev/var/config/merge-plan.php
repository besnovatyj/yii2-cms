<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/**
 * BOOTSTRAP merge-plan (root-слой скелета, БЕЗ вкладов модулей).
 *
 * Назначение — разорвать «курицу-яйцо» первого запуска: `yii` бутстрапится через
 * {@see \common\config\ConfigFactory}, которому нужен `var/config/merge-plan.php`, а полный план
 * генерит `Modman/modules/recompile` — который сам поднимает `yii`. На чистом сервере этого файла ещё
 * нет. `init` разворачивает этот шаблон в `var/config/merge-plan.php` (обычным copyFile, как *-local.php),
 * и его достаточно, чтобы поднять консоль: ядро (db/log/queue) живёт в root-файлах, а системный Modman
 * саморегистрируется из своего Bootstrap независимо от артефактов. Первый же `recompile` ПЕРЕЗАПИШЕТ этот
 * файл полным планом (с вкладами модулей), поэтому здесь только root-слой.
 *
 * Файл env-агностичен и портабелен (все пути package-relative, ссылок на набор модулей нет), поэтому
 * лежит идентичным в dev и prod. Держать в обоих окружениях ОБЯЗАТЕЛЬНО: remove-фаза `init` удаляет файлы,
 * присутствующие в другом окружении и отсутствующие в текущем.
 *
 * НЕ редактировать руками структуру групп — при рассинхроне с реальным планом (см. var/config/merge-plan.php
 * после recompile) правьте здесь только root-слой.
 */

return [
    '/' => [
        // Меню админки наполняется исключительно вкладами модулей — на холодном старте пусто.
        'admin-menu' => [],
        'app-backend' => [
            '/' => [
                '$common',
                'backend/config/main.php',
                '?backend/config/main-local.php',
            ],
        ],
        'app-console' => [
            '/' => [
                '$common',
                'console/config/main.php',
                '?console/config/main-local.php',
            ],
        ],
        'app-frontend' => [
            '/' => [
                '$common',
                'frontend/config/main.php',
                '?frontend/config/main-local.php',
            ],
        ],
        'app-rest' => [
            '/' => [
                '$common',
                'rest/config/main.php',
                '?rest/config/main-local.php',
            ],
        ],
        'common' => [
            '/' => [
                'common/config/main.php',
                '?common/config/main-local.php',
            ],
        ],
        'params' => [
            '/' => [
                'common/config/params.php',
                '?common/config/params-local.php',
            ],
        ],
    ],
];
