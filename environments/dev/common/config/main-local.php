<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Helpers\SecretReader;

return [
    'components' => [
        'db' => [
            'class' => \yii\db\Connection::class,
            // TODO На проде убедиться, что Yii2 ходит в БД через unix-socket!!!
            'dsn' => 'mysql:host=mysql;dbname=' . SecretReader::get('MYSQL_DATABASE'), // TODO - на данном этапе на проде сделать через .env с обновлением после рестарта сервера
            'username' => SecretReader::get('MYSQL_USER'), // TODO - на данном этапе на проде сделать через .env с обновлением после рестарта сервера
            'password' => SecretReader::get('mysql_password'), // TODO - на данном этапе на проде сделать через .env с обновлением после рестарта сервера
            'charset' => 'utf8mb4',
            'enableSchemaCache' => !YII_DEBUG,
            'schemaCacheDuration' => 3600,
            'enableLogging' => YII_DEBUG,
            'enableProfiling' => YII_DEBUG,
        ],
        'redis' => [
            'class' => yii\redis\Connection::class,
            'hostname' => 'redis',
            'port' => 6379,
            'database' => 0,
            'password' => SecretReader::get('redis_password'), // TODO - на данном этапе на проде сделать через .env с обновлением после рестарта сервера
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@common/mail',
            // Для локальной разработки используем Mailpit (перехватывает все письма)
            'useFileTransport' => false,
            'transport' => [
                // Mailpit SMTP server (контейнер mailpit в docker-compose.yml)
                // host: mailpit - имя сервиса в docker-compose
                // port: 1025 - SMTP порт Mailpit
                // Аутентификация не требуется (MP_SMTP_AUTH_ACCEPT_ANY=1)
                'dsn' => 'smtp://mailpit:1025',
            ],
            //
            // Для продакшена используй реальный SMTP сервер:
            //
            // SMTP server example:
            //    'transport' => [
            //        'scheme' => 'smtps',
            //        'host' => 'smtp.example.com',
            //        'username' => 'your_username',
            //        'password' => 'your_password',
            //        'port' => 465,
            //    ],
            //
            // DSN example:
            //    'transport' => [
            //        'dsn' => 'smtp://user:pass@smtp.example.com:25',
            //    ],
            //
            // See: https://symfony.com/doc/current/mailer.html#using-built-in-transports
            // Or if you use a 3rd party service, see:
            // https://symfony.com/doc/current/mailer.html#using-a-3rd-party-transport
        ],
    ],
];
