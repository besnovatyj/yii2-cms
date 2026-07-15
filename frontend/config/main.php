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
$isSecure = parse_url($params['frontendHostName'], PHP_URL_SCHEME) === 'https';

// frontend — открытое приложение (гейта нет). identityClass отдаёт модуль user через config-plugin
// группы app-frontend (merge-plan yiisoft/config), поэтому merge appConfigFile здесь не нужен.
return [
    'id' => 'app-frontend',
    'charset' => 'utf-8',
    // 'language' задаётся в common/config/main.php ('ru-RU'); дубль здесь ломал бы сборку
    // yiisoft/config (Duplicate key в одном слое — define once per layer).
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'frontend\controllers',
    //'defaultRoute' => 'moduleName/Controller/Action?id=31',
    'components' => [
        'assetManager' => [
            'bundles' => require __DIR__ . '/bundles-redefinition.php',
        ],
        'request' => [
            'csrfParam' => '_csrf-frontend',
            'cookieValidationKey' => $params['cookieValidationKey'],
        ],
        'user' => [
            'class' => \yii\web\User::class,
            // identityClass НЕ задаём в ядре — его отдаёт модуль user (vendor) через config-plugin app-frontend.
            'enableAutoLogin' => true, //enable cookie-based login
            'identityCookie' => [
                'name' => '_identity',
                'httpOnly' => true, // Должен ли файл cookie быть доступен только через протокол HTTP. Если для этого свойства установлено значение true, cookie не будет доступен для языков сценариев, таких как JavaScript, что может эффективно помочь уменьшить кражу личных данных с помощью XSS-атак.
                'sameSite' => PHP_VERSION_ID >= 70300 ? yii\web\Cookie::SAME_SITE_LAX : null,
                'domain' => $params['cookieDomain'],
                'secure' => $isSecure,
            ],
        ],
        'session' => [
            'name' => '_session',
            'cookieParams' => [
                'domain' => $params['cookieDomain'],
                'httpOnly' => true, // Должен ли файл cookie быть доступен только через протокол HTTP. Если для этого свойства установлено значение true, cookie не будет доступен для языков сценариев, таких как JavaScript, что может эффективно помочь уменьшить кражу личных данных с помощью XSS-атак.
                'sameSite' => PHP_VERSION_ID >= 70300 ? yii\web\Cookie::SAME_SITE_LAX : null,
                'secure' => $isSecure,
            ],
        ],
        // Компонент темизации `view.theme` НЕ задаётся здесь: темизация не входит в минимальные
        // зависимости ядра, поэтому её подключает сам пакет `besnovatyj/yii2-cms-themes` вкладом
        // config-plugin (группа app-frontend). Без пакета фронт рендерит `@app/views` напрямую —
        // мягкая деградация, а не фатал (см. ANALYSIS_MODULES_INTEGRATION.MD, П8).
        'log' => [
            'traceLevel' => YII_DEBUG ? 10 : 0,
            'targets' => [
                'frontFile' => [ // все ошибки фронтэнда пишем в файл
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                    'logFile' => '@runtime/logs/front.log',
                    'maxFileSize' => 10240, // 10MB
                    'maxLogFiles' => 5,
                ],
                'frontEmail' => [ // все ошибки фронтэнда кроме 404 (поисковики бесят, надо сайтмап делать) на почту
                    'class' => \yii\log\EmailTarget::class,
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
        'urlManager' => static function () {
            return Yii::$app->get('frontendUrlManager');
        },
    ],
    'params' => $params,
];
