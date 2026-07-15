<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace backend\views\assets;

use yii\web\AssetBundle;

class AdmAssets extends AssetBundle
{
    public $sourcePath = __DIR__ . '/media';

    public $depends = [
        HeaderAssets::class,
        FooterAssets::class,
    ];
}
