<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

$params = require __DIR__ . '/../../common/config/params-local.php';

use yii\web\UrlManager;

return [
    'class' => UrlManager::class,
    'hostInfo' => $params['restHostName'],
    'enablePrettyUrl' => true,
    'enableStrictParsing' => true, // Доступны только те адреса, которые описаны ниже, никакой магии Yii2
    'showScriptName' => false,
    'rules' => [
        '' => 'site', // Без этого не работает базовый контроллёр

        'GET menu/rest/menu/index' => 'menu/rest/menu/index',

        'POST oauth2/token' => 'o-auth2/token',
        'GET site/ping' => 'site/ping',

        'profile' => 'user/rest/profile/index',

//        'POST oauth2/<action:\w+>' => 'oauth2/rest/<action>',
//
//        'GET shop/products/<id:\d+>' => 'shop/product/view',
//        'GET shop/products/category/<id:\d+>' => 'shop/product/category',
//        'GET shop/products/brand/<id:\d+>' => 'shop/product/brand',
//        'GET shop/products/tag/<id:\d+>' => 'shop/product/tag',
//        'GET shop/products' => 'shop/product/index',
//        'shop/products/<id:\d+>/cart' => 'shop/cart/add',
//        'shop/products/<id:\d+>/wish' => 'shop/wishlist/add',
//
//        'GET shop/cart' => 'shop/cart/index',
//        'DELETE shop/cart' => 'shop/cart/clear',
//        'shop/cart/checkout' => 'shop/checkout/index',
//        'PUT shop/cart/<id:\w+>/quantity' => 'shop/cart/quantity',
//        'DELETE shop/cart/<id:\w+>' => 'shop/cart/delete',
//
//        'GET shop/wishlist' => 'shop/wishlist/index',
//        'DELETE shop/wishlist/<id:\d+>' => 'shop/wishlist/delete',
//        [
//            'class' => 'yii\rest\UrlRule',
//            'controller' => 'modules/menuRest/controllers/rest/MenuController',
//        ],
    ],
];
