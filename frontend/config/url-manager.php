<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/** @var $params array */

return [
    'rules' => [
        // CSP Reports (без авторизации, без CSRF)
        'POST csp-report' => 'csp-report/index',

        /** Куда перенаправлять с главной */
        // '' => 'shop/catalog/main',
        // ['pattern' => '', 'route' => 'page/page/view', 'defaults' => ['id' => '31']],

        /** Глобальные правила, если ничего выше не сработало */
        '<_c:[\w\-]+>' => '<_c>/index',
        '<_c:[\w\-]+>/<id:\d+>' => '<_c>/view',
        '<_c:[\w\-]+>/<_a:[\w-]+>' => '<_c>/<_a>',
        '<_c:[\w\-]+>/<id:\d+>/<_a:[\w\-]+>' => '<_c>/<_a>',

    ],
];
