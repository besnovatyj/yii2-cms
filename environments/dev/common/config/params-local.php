<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Helpers\SecretReader;

$config = [
    'frontendHostName' => 'https://' . SecretReader::get('MAIN_HOST'), // TODO - на данном этапе на проде сделать через .env с обновлением после рестарта сервера
    'backendHostName' => 'https://' . SecretReader::get('ADM_HOST'), // TODO - на данном этапе на проде сделать через .env с обновлением после рестарта сервера
    'staticHostName' => 'https://' . SecretReader::get('FILES_HOST'), // TODO - на данном этапе на проде сделать через .env с обновлением после рестарта сервера
    'restHostName' => 'https://' . SecretReader::get('REST_HOST'), // TODO - на данном этапе на проде сделать через .env с обновлением после рестарта сервера
    'cookieValidationKey' => '',
    'cookieDomain' => '.' . SecretReader::get('MAIN_HOST'), // TODO - на данном этапе на проде сделать через .env с обновлением после рестарта сервера
    'logEmailFrom' => 'logEmailFrom@test.loc',
    'logEmailTo' => 'logEmailTo@test.loc',
    'invisible-recaptcha-key' => SecretReader::get('recaptcha_key'), // TODO - на данном этапе на проде сделать через .env с обновлением после рестарта сервера
    'invisible-recaptcha-secret'  => SecretReader::get('recaptcha_secret'), // TODO - на данном этапе на проде сделать через .env с обновлением после рестарта сервера
];

Yii::setAlias('@staticHostName', $config['staticHostName']);
Yii::setAlias('@restHostName', $config['restHostName']);

return $config;
