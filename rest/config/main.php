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

// identityClass=Identity для rest отдаёт модуль user через config-plugin группы app-rest
// (merge-plan yiisoft/config). Без модуля rest закрыт: гость не проходит as authenticator/as access,
// а токены некому валидировать. Merge appConfigFile здесь больше не нужен.
return [
    'id' => 'app-rest',
    'charset' => 'utf-8',
    // 'language' задаётся в common/config/main.php ('ru-RU'); дубль здесь ломал бы сборку
    // yiisoft/config (Duplicate key в одном слое — define once per layer).
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'rest\controllers',
    //'defaultRoute' => 'moduleName/Controller/Action?id=31',
    'bootstrap' => [
        [
            // Автоматически по заголовкам определяет формат ответа
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
            // TODO - вынести ключи в Docker Secrets
            'privateKeyPath' => '@app/../common/components/oauth2/keys/private.key',
            'privateKeyPassphrase' => null,
            // TODO - вынести генерацию в Docker Compose или в Environments
            'encryptionKey' => '9pep+IghEy4s96qyvWFenl7t+T+P5p072WtwgEQxncM=', // ЗАМЕНИТЕ НА СВОЙ! base64_encode(random_bytes(32))
            'accessTokenTTL' => 3600, // 1 час
            'refreshTokenTTL' => 2592000, // 30 дней
            'keyPermissionsCheck' => !YII_DEBUG, // Отключить проверку прав в dev (для WSL2/Docker)
        ],

        // OAuth2 Resource Server (для валидации токенов)
        'oauth2ResourceServer' => [
            'class' => \Besnovatyj\Oauth2\ResourceServer::class,
            'publicKeyPath' => '@app/../common/components/oauth2/keys/public.key',
            'keyPermissionsCheck' => !YII_DEBUG, // Отключить проверку прав в dev (для WSL2/Docker)
        ],

        'request' => [
            'enableCsrfValidation' => false, // REST API не использует CSRF
            'cookieValidationKey' => 'dummy-key-for-rest-api', // Требуется Yii2, но не используется
            'parsers' => [
                // Если запрос пришёл одной JSON строкой, то это позволит фреймворку его распарсить в Yii::$app->request->queryParams
                'application/json' => 'yii\web\JsonParser',
            ]
        ],

        'response' => [
            'formatters' => [
                // Форматирует ответ сервера
                'json' => [
                    'class' => 'yii\web\JsonResponseFormatter',
                    'prettyPrint' => YII_DEBUG, // Отдаёт ответ в красивом формате с пробелами и переносами
                    'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                ],
            ],
        ],

        'user' => [
            'class' => \yii\web\User::class,
            // identityClass НЕ задаём в ядре — его отдаёт модуль user (vendor) через config-plugin app-rest.
            'enableAutoLogin' => false,
            'enableSession' => false,
        ],

        'log' => [
            'traceLevel' => YII_DEBUG ? 10 : 0,
            'targets' => [
                'restFile' => [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning', 'info'],
                    'logFile' => '@runtime/logs/rest.log',
                    'maxFileSize' => 10240, // 10MB
                    'maxLogFiles' => 5,
                ],
            ],
        ],

        'urlManager' => require __DIR__ . '/url-manager.php',
    ],

    // Аутентификация через Bearer токен или query параметр
    'as authenticator' => [
        'class' => 'yii\filters\auth\CompositeAuth',
        'except' => ['site/index', 'o-auth2/token'], // смотри настройки UrlManager
        'authMethods' => [
            ['class' => 'yii\filters\auth\HttpBearerAuth'],
            ['class' => 'yii\filters\auth\QueryParamAuth', 'tokenParam' => 'accessToken'],
        ]
    ],

    'as access' => [ // TODO - возможно, RBAC как в бэкэнде, или как это реализовать в REST, свои проверки какие-то?
        'class' => 'yii\filters\AccessControl',
        'except' => ['site/index', 'o-auth2/token'],
        'rules' => [
            [
                'allow' => true,
                'roles' => ['@'],
            ],
        ],
    ],
    'params' => $params,
];
