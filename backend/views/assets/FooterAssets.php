<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace backend\views\assets;

use yii\web\AssetBundle;
use yii\web\View;

class FooterAssets extends AssetBundle
{
    public $sourcePath = __DIR__ . '/media';

    public $css = [];

    public $js = [
        // Bootstrap 5 + Popper → window.bootstrap (собирается через esbuild)
        'dist/js/bootstrap.js',
        // htmx, tooltips, grid-view — использует window.bootstrap
        'dist/js/index.js',
    ];

    public $jsOptions = ['position' => View::POS_END];

}
