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
            // Обратный адрес по умолчанию для ВСЕХ писем приложения. Symfony Mailer требует заголовок
            // From (или Sender) — без него любое письмо падает с "An email must have a "From" or a
            // "Sender" header." Модули-отправители (Contact и др.) свой From не задают: это общая
            // настройка common-слоя, а не свойство модуля. Адрес должен быть на домене сайта, иначе
            // SPF/DKIM/DMARC у получателя отбракуют письмо.
            'messageConfig' => [
                'from' => ['noreply@example.com' => 'Example.com'],
            ],
            // send all mails to a file by default.
            'useFileTransport' => true,
            'fileTransportCallback' => static function ($mailer, $message): string {
                $dir = date('Y-m-d/H'); // 'Y-m-d/H' — шардирование по часам
                $base = Yii::getAlias($mailer->fileTransportPath);
                \yii\helpers\FileHelper::createDirectory($base . '/' . $dir); // ВАЖНО, см. ниже
                return $dir . '/' . date('His') . '-' . uniqid() . '.eml';
            },
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
