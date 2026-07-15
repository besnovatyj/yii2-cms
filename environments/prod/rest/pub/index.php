<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_ENV') or define('YII_ENV', 'prod');

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../../common/config/bootstrap.php';
require __DIR__ . '/../config/bootstrap.php';

// Конфиг собирается движком yiisoft/config (Yii3) по merge-plan, который генерит modman из реестра
// активных модулей. Группа = приложение (app-rest). Замыкания в файлах модулей require-ятся как есть.
$config = (new \common\config\ConfigFactory())->app('app-rest');

(new yii\web\Application($config))->run();
