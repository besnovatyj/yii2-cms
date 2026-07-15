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

//        'contact' => 'contact/contact/index',
        // Правила модуля User (вход/регистрация/кабинет) перенесены в сам модуль
        // (frontendUrlManager группы `common`, роут → 'User/...'): vendor/besnovatyj/yii2-cms-user.

        // Правила модуля Blog перенесены в сам модуль (вклад в frontendUrlManager группы `common`,
        // роут капитализирован до 'Blog'): vendor/besnovatyj/yii2-cms-blog/src/config/common.php.
        // Гейтятся modman — деактивация модуля убирает их из сборки. Пилот канала «URL-правила = вклад».

        // Правила модулей Actors / Performance / Documents перенесены в сами модули
        // (frontendUrlManager группы `common`, роуты → 'Actors/...', 'Performance/...', 'Documents/...'):
        // vendor/besnovatyj/yii2-cms-{actors,performance,documents}. Гейтятся modman.

        // ['pattern' => 'sitemap-html', 'route' => 'sitemap/sitemap/index-html'],

        // ['pattern' => 'sitemap', 'route' => 'sitemap/sitemap/index', 'suffix' => '.xml'],
        // ['pattern' => 'sitemap-<target:[a-z-]+>-<start:\d+>', 'route' => 'sitemap/sitemap/<target>', 'suffix' => '.xml'],
        // ['pattern' => 'sitemap-<target:[a-z-]+>', 'route' => 'sitemap/sitemap/<target>', 'suffix' => '.xml'],

        // 'catalog' => 'shop/catalog/index',
        // ['class' => 'modules\shop\urls\CategoryUrlRule'],
        // 'catalog/<id:\d+>' => 'shop/catalog/product',

        // 'cabinet/*' — перенесено в модуль User (см. выше).

        /** Глобальные правила, если ничего выше не сработало */
        '<_c:[\w\-]+>' => '<_c>/index',
        '<_c:[\w\-]+>/<id:\d+>' => '<_c>/view',
        '<_c:[\w\-]+>/<_a:[\w-]+>' => '<_c>/<_a>',
        '<_c:[\w\-]+>/<id:\d+>/<_a:[\w\-]+>' => '<_c>/<_a>',

    ],
];
