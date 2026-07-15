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
            'enableSchemaCache' => true,
            'schemaCacheDuration' => 3600,
            'enableLogging' => false,
            'enableProfiling' => false,
        ],
        'redis' => [
            'class' => yii\redis\Connection::class,
            'hostname' => 'redis',
            'port' => 6379,
            'database' => 0,
            'password' => SecretReader::get('redis_password'), // TODO - на данном этапе на проде сделать через .env с обновлением после рестарта сервера
        ],
        'mailer' => [ // TODO - Если не работает, не блокирует ли фаервол порты?
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@common/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
            // You have to set
            //
            // 'useFileTransport' => false,
            //
            // and configure a transport for the mailer to send real emails.
            //
            // SMTP server example:
            //    'transport' => [
            //        'scheme' => 'smtps',
            //        'host' => '',
            //        'username' => '',
            //        'password' => '',
            //        'port' => 465,
            //        'dsn' => 'native://default',
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
