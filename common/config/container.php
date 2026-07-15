<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use yii\base\ErrorHandler;
use yii\caching\Cache;
use yii\mail\MailerInterface;
use yii\queue\Queue;
use yii\rbac\ManagerInterface;

/**
 * Перенесены все регистрации из SetUp.php:
 * - MailerInterface, ErrorHandler, Queue, Cache, ManagerInterface — через замыкания с Yii::$app вместо $app
 * (всё равно одно и то же, просто через статик вместо захваченной переменной)
 */

// Базовая конфигурация DIC приложения
return [
    'definitions' => [
//        'yii\widgets\LinkPager' => ['maxButtonCount' => 5]
    ],
    'singletons' => [
        // Конфигурация для единожды создающихся объектов
        MailerInterface::class => function () {
            return Yii::$app->mailer;
        },
        ErrorHandler::class => function () {
            return Yii::$app->errorHandler;
        },
        Queue::class => function () {
            return Yii::$app->get('queue');
        },
        Cache::class => function () {
            return Yii::$app->cache;
        },
        ManagerInterface::class => function () {
            return Yii::$app->authManager;
        },
    ]
];
