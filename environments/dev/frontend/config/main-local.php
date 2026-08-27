<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

$config = [];

if (!YII_ENV_TEST) {
    // TLS терминирует Traefik, дальше в nginx трафик идёт по HTTP (compose/application.yml —
    // traefik.http.services.nginx-service.loadbalancer.server.port=80). Без доверия к прокси Yii
    // вырезает X-Forwarded-Proto (\yii\web\Request::filterHeaders), считает соединение
    // незащищённым и отдаёт hostInfo вида http://..., хотя браузер работает по https.
    // Доверяем приватным диапазонам docker-сетей: снаружи в контейнер nginx можно попасть
    // только через Traefik. Подсети у compose динамические (в compose/resources.yml без ipam),
    // поэтому диапазоны заданы целиком. Только для dev — в проде прокси перед nginx нет.
    $config['components']['request']['trustedHosts'] = [
        '172.16.0.0/12',
        '192.168.0.0/16',
    ];

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
    ];
}

return $config;
