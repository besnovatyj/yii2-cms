<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',

    // Генерируемые modman файлы конфигурации. modules/components/bootstrap/appConfig после переезда
    // на yiisoft/config собираются движком по merge-plan (config-plugin) и здесь не объявляются.
    // Остаётся registry-gated канал лога устанавливаемых модулей (см. common/config/log.php).
    'logChannelsConfigFile' => '@config-dyn-gen/logChannelsConfigFile.php',
    'frontThemeConfigFile' => '@config-dyn-gen/frontThemeConfig.php',
    // Базовый путь кэша карты представлений. Реальные файлы — тема-зависимые:
    // `themePathMap.{themeName}.php` (в той же директории). См. ThemePathMapService.
    'themePathMap' => '@config-dyn-gen/themePathMap.php',
    // Тема-НЕзависимый манифест источников представлений модулей (генерит modman при recompile).
    // Должен совпадать с modman `artifacts.viewSources`.
    'moduleViewSourcesFile' => '@config-dyn-gen/moduleViewSources.php',
];
