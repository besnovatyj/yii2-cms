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

// TODO В NGINX переадресация HTTP => HTTPS
$isSecure = parse_url($params['backendHostName'], PHP_URL_SCHEME) === 'https';

// Вклад модулей (identityClass=Identity, accessAuthorizer=RbacAuthorizer, whitelist входа/challenge)
// приходит через merge-plan yiisoft/config из групп app-backend их config-plugin (user, altcha, …),
// поэтому здесь ArrayHelper::merge appConfigFile больше не нужен.
return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'charset' => 'utf-8',
//    'bootstrap' => [],
    'components' => [
        'assetManager' => [
            'bundles' => require __DIR__ . '/bundles-redefinition.php',
        ],
        'request' => [
            'csrfParam' => '_csrf-backend',
            'cookieValidationKey' => $params['cookieValidationKey'],
        ],
        'user' => [
            'class' => \yii\web\User::class,
            // identityClass/loginUrl НЕ задаём в ядре: их отдаёт модуль user (слой vendor) через
            // config-plugin app-backend. Без модуля user идентичности нет — гейт fail-closed (deny).
            'enableAutoLogin' => true, //enable cookie-based login
            'identityCookie' => [
                'name' => '_identity',
                'httpOnly' => true, // Должен ли файл cookie быть доступен только через протокол HTTP. Если для этого свойства установлено значение true, cookie не будет доступен для языков сценариев, таких как JavaScript, что может эффективно помочь уменьшить кражу личных данных с помощью XSS-атак.
                'sameSite' => PHP_VERSION_ID >= 70300 ? \yii\web\Cookie::SAME_SITE_STRICT : null,
                'domain' => $params['cookieDomain'],
                'secure' => $isSecure,
            ],
        ],
        'session' => [
            'name' => '_session',
            'cookieParams' => [
                'domain' => $params['cookieDomain'],
                'httpOnly' => true, // Должен ли файл cookie быть доступен только через протокол HTTP. Если для этого свойства установлено значение true, cookie не будет доступен для языков сценариев, таких как JavaScript, что может эффективно помочь уменьшить кражу личных данных с помощью XSS-атак.
                'sameSite' => PHP_VERSION_ID >= 70300 ? \yii\web\Cookie::SAME_SITE_STRICT : null,
                'secure' => $isSecure,
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 10 : 0,
            'targets' => [
                'backFile' => [ // все ошибки бэкэнда пишем в файл
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                    'logFile' => '@runtime/logs/back.log',
                    'maxFileSize' => 10240, // 10MB
                    'maxLogFiles' => 5,
                ],
                'backEmail' => [ // все ошибки бэкэнда отправляем на почту (переделать на телегу)
                    'class' => \yii\log\EmailTarget::Class,
                    'levels' => ['error', 'warning'],
                    'except' => [
//                        'yii\web\HttpException:404',
//                        'yii\web\HttpException:400',
                    ],
                    'message' => ['from' => $params['logEmailFrom'], 'to' => $params['logEmailTo']],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => function () {
            return Yii::$app->get('backendUrlManager');
        },
    ],
    // Гейт закрытого приложения — ЯДРОВЫЙ и неотъемлемый: класс задаётся здесь и не может быть заменён
    // модулем (allowlist-политика modman режет as access.class у вкладов). Whitelist — минимум ядра;
    // маршруты входа/выхода домешивает модуль user через ProvidesAppConfig (as access.allowActions).
    'as access' => [
        'class' => \Besnovatyj\Kernel\security\DefaultDenyAccessControl::class,
        'allowActions' => [
            // Ядровый минимум. Маршруты модулей (вход user, challenge altcha, …) домешиваются
            // самими модулями через config-plugin (as access.allowActions конкатенируется по слоям).
            'site/error',
        ],
    ],
    'params' => $params,
];
