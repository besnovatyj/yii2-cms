<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace themes\basic\assets;

use yii\web\AssetBundle;

class ThemeAssets extends AssetBundle
{
    public $sourcePath = __DIR__ . '/media';
    public $css = [
        'vendor/bootstrap/css/bootstrap.min.css',
        'css/theme.css',
    ];

    public $js = [
        'vendor/bootstrap/js/bootstrap.bundle.min.js',
        'js/theme.js',
    ];
}
