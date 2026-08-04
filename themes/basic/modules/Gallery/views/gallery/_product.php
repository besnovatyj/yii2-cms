<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/* @var $this yii\web\View */

/* @var $product \modules\gallery\entities\Gallery\Gallery */

use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;

$url = Url::to(['gallery', 'id' => $model->id]);

?>

<div class="item-gallery isotope-item bo-rad-10 hov-img-zoom">
    <a href="<?= Html::encode($url) ?>">
        <img src="<?= Html::encode($model->mainPhoto->getThumbFileUrl('file', 'catalog_list')) ?>" alt="">
    </a>
</div>
