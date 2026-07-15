<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/**
 * Пример конфигурации REST приложения с новым OAuth2 Server
 * Замените содержимое app/rest/config/main.php на этот конфиг (адаптировав под свои нужды)
 */

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-rest',
    'charset' => 'utf-8',
    'language' => 'ru',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'rest\controllers',
    'bootstrap' => [
        [
            'class' => 'yii\filters\ContentNegotiator',
            'formats' => [
                'application/json' => 'json',
                'application/xml' => 'xml',
            ],
        ],
    ],
    'components' => [
        // OAuth2 Authorization Server (для выдачи токенов)
        'oauth2AuthServer' => [
            'class' => \Besnovatyj\Oauth2\AuthorizationServer::class,
            'privateKeyPath' => '@app/../common/components/oauth2/keys/private.key',
            'privateKeyPassphrase' => null,
            'encryptionKey' => 'CgLGM0feNVnu7Wpeqi8wdJkIg4bChK0N0wt2gXvG5zc=', // ЗАМЕНИТЕ НА СВОЙ! base64_encode(random_bytes(32))
            'accessTokenTTL' => 3600, // 1 час
            'refreshTokenTTL' => 2592000, // 30 дней
            'keyPermissionsCheck' => !YII_DEBUG, // Отключить проверку прав в dev (для WSL2/Docker)
        ],

        // OAuth2 Resource Server (для валидации токенов)
        'oauth2ResourceServer' => [
            'class' => \Besnovatyj\Oauth2\ResourceServer::class,
            'publicKeyPath' => '@app/../common/components/oauth2/keys/public.key',
        ],

        'request' => [
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],

        'response' => [
            'formatters' => [
                'json' => [
                    'class' => 'yii\web\JsonResponseFormatter',
                    'prettyPrint' => YII_DEBUG,
                    'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                ],
            ],
        ],

        'user' => [
            'class' => \yii\web\User::class,
            'identityClass' => \Besnovatyj\User\entities\Identity::class,
            'enableAutoLogin' => false,
            'enableSession' => false,
        ],

        'log' => [
            'traceLevel' => YII_DEBUG ? 10 : 0,
            'targets' => [
                'restFile' => [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                    'logFile' => '@runtime/logs/rest.log',
                    'maxFileSize' => 10240,
                    'maxLogFiles' => 5,
                ],
            ],
        ],

        'urlManager' => require __DIR__ . '/url-manager.php',
    ],

    // Аутентификация через Bearer токен или query параметр
    'as authenticator' => [
        'class' => 'yii\filters\auth\CompositeAuth',
        'except' => ['site/index', 'o-auth2/token'], // ВАЖНО: o-auth2 (kebab-case), не oauth2!
        'authMethods' => [
            ['class' => 'yii\filters\auth\HttpBearerAuth'],
            ['class' => 'yii\filters\auth\QueryParamAuth', 'tokenParam' => 'accessToken'],
        ]
    ],

    // Контроль доступа
    'as access' => [
        'class' => 'yii\filters\AccessControl',
        'except' => ['site/index', 'o-auth2/token'], // ВАЖНО: o-auth2 (kebab-case), не oauth2!
        'rules' => [
            [
                'allow' => true,
                'roles' => ['@'],
            ],
        ],
    ],

    'params' => $params,
];
