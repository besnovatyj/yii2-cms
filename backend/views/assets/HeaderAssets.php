<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace backend\views\assets;

use yii\web\AssetBundle;
use yii\web\View;

class HeaderAssets extends AssetBundle
{
    public $sourcePath = __DIR__ . '/media';

    public $css = [
        // Bootstrap 5 + Bootstrap Icons + Custom CSS (собирается через esbuild/sass)
        'dist/css/adm.css',
    ];

    public $js = [];

    public $jsOptions = ['position' => View::POS_HEAD];

}
