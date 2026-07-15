<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [
        'log',
    ],
    'controllerNamespace' => 'console\controllers',
    'controllerMap' => [
        'fixture' => [
            'class' => \yii\console\controllers\FixtureController::class,
            'namespace' => 'common\fixtures',
          ],
    ],
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                    'logFile' => '@runtime/logs/console.log',
                ],
//                'allDatabase' => [
//                    'class' => yii\log\DbTarget::class,
//                    'levels' => ['error', 'warning'],
//                    'logTable' => '{{%log_logs}}',
//                ],
            ],
        ],
    ],
    'params' => $params,
];
