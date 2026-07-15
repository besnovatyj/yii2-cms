<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

$config = [];

if (!YII_ENV_TEST) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => \yii\debug\Module::class,
        'allowedIPs' => ['172.*','192.*'],
        'panels' => [
            'queue' => \yii\queue\debug\Panel::class,
            'appConfig' => \Besnovatyj\DebugPanelModules\Panel::class,
        ],
        'traceLine' => '<a href="phpstorm://open?url={file}&line={line}">{file}:{line}</a>'
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => \yii\gii\Module::class,
        'allowedIPs' => ['172.*','192.*'],
    ];
}

return $config;
