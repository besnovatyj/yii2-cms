<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Helpers\SecretReader;

$config = [
    'frontendHostName' => 'https://' . SecretReader::get('MAIN_HOST'),
    'backendHostName' => 'https://' . SecretReader::get('ADM_HOST'),
    'staticHostName' => 'https://' . SecretReader::get('FILES_HOST'),
    'cookieValidationKey' => '',
    'cookieDomain' => '.' . SecretReader::get('MAIN_HOST'),
    'logEmailFrom' => 'logEmailFrom@test.loc',
    'logEmailTo' => 'logEmailTo@test.loc',
    'invisible-recaptcha-key' => SecretReader::get('RECAPTCHA_KEY'),
    'invisible-recaptcha-secret'  => SecretReader::get('RECAPTCHA_SECRET'),
];

Yii::setAlias('@staticHostName', $config['staticHostName']);

return $config;
