<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Helpers\SecretReader;

return [
    'components' => [
        'db' => [
            'class' => \yii\db\Connection::class,
            'dsn' => 'mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=' . SecretReader::get('MYSQL_DATABASE'),
            'username' => SecretReader::get('MYSQL_USER'),
            'password' => SecretReader::get('MYSQL_PASSWORD'),
            'charset' => 'utf8mb4',
            'enableSchemaCache' => true,
            'schemaCacheDuration' => 3600,
            'enableLogging' => false,
            'enableProfiling' => false,
        ],
        'redis' => [
            'class' => yii\redis\Connection::class,
            'hostname' => '127.0.0.1',
            'port' => 6379,
            'database' => 0,
            'password' => SecretReader::get('REDIS_PASSWORD'),
        ],
        'mailer' => [ // Если не работает, не блокирует ли фаервол порты?
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@common/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
            // Необходимо установить ('useFileTransport' => true) и настроить транспорт для реальной отправки писем.
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
