<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/** @var $params array */

return [
    'assetManager' => [
        'linkAssets' => YII_DEBUG,
    ],
    'queue' => [
        'class' => \yii\queue\redis\Queue::class,
//        'class' => \yii\queue\file\Queue::class,
        'as log' => \yii\queue\LogBehavior::class,
    ],
    'cache' => [
        'class' => \yii\caching\ApcCache::class,
        'useApcu' => true,
    ],
    'session' => [
        'class' => \yii\redis\Session::class,
//        'class' => \yii\web\Session::class,
    ],
    'frontendUrlManager' => [
        'class' => \yii\web\UrlManager::class,
        'hostInfo' => (require __DIR__ . '/params-local.php')['frontendHostName'], // TODO - Костыль какой-то, надо разобраться с наследованием и подключением конфигов
        'baseUrl' => '',
        'enablePrettyUrl' => true,
        //'enableStrictParsing' => true,
        'showScriptName' => false,
        // Кэш правил ВЫКЛЮЧЕН намеренно: модули вкладывают DI-конструируемые класс-правила
        // (UrlRuleInterface, напр. CategoryUrlRule/TaxonomyUrlRule) — объект-правило с сервисами
        // не сериализуется в кэш правил UrlManager. Без этого — фатал при сборке кэша правил.
        'cache' => false,
    ],
    'backendUrlManager' => [
        'class' => \yii\web\UrlManager::class,
        'hostInfo' => (require __DIR__ . '/params-local.php')['backendHostName'], // TODO - Костыль какой-то, надо разобраться с наследованием и подключением конфигов
        'baseUrl' => '',
        'enablePrettyUrl' => true,
        'showScriptName' => false,
//    'rules' => [
//        '' => 'site/index',
//        '<_a:login|logout>' => '/user/backend/auth/<_a>',
//
//        '<_c:[\w\-]+>' => '<_c>/index',
//        '<_c:[\w\-]+>/<id:\d+>' => '<_c>/view',
//        '<_c:[\w\-]+>/<_a:[\w\-]+>' => '<_c>/<_a>',
//        '<_c:[\w\-]+>/<id:\d+>/<_a:[\w\-]+>' => '<_c>/<_a>',
//    ],
    ],
    // accessAuthorizer НЕ задаём здесь: гейт ядра fail-closed сам по себе — если компонент не резолвится
    // (нет модуля безопасности), DefaultDenyAccessControl трактует это как ЗАПРЕТ. Задаёт его ровно один
    // слой — модуль user через ProvidesAppConfig (per-app, RbacAuthorizer). Дефолт в common дублировал бы
    // ключ components.accessAuthorizer.class с app-слоем → Merger yiisoft/config это запрещает (define once).
    // authManager тоже не здесь — он в модуле user (ProvidesComponents), RBAC-таблицы принадлежат ему.
];
