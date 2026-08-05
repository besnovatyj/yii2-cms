<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use modules\actors\entities\actors\Actor;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $model Actor */

$url = Url::to(['view', 'uuid' => $model->uuid]);
$emptyActorUrl = Url::to(['/static_assets_bd/images/actors/empty-actor.svg'], true);
$imgUrl = isset($model->mainImage) ? $model->mainImage->getThumbUrl('file', 'frontend_list', $emptyActorUrl) : $emptyActorUrl;

?>

<!--<a href="" class="item lightbox-link hover-zoom">-->
<a href="<?= Html::encode($url) ?>" class="item hover-zoom">
    <div class="text-wrapper text-center">
        <h3 class="title text-size-0 white text-shadowed"><?= $model->name ?></h3>
    </div>
    <div class="image-wrapper shadow rounded">
        <div class="overlay tertiary-25"></div>
        <img src="<?= $imgUrl ?>" class="image" alt="<?= $model->name ?>"/>
    </div>
</a>
