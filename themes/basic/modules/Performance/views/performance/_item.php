<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use modules\performance\entities\performance\Performance;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $model Performance*/

$url = Url::to(['/performance/view/' . $model->uuid]);
$emptyPerformancePosterUrl = Url::to(['/static_assets_bd/images/performances/empty-performance.svg'], true);
$imgUrl = isset($model->mainImage) ? $model->mainImage->getThumbFileUrl('file', 'frontend_list', $emptyPerformancePosterUrl) : $emptyPerformancePosterUrl;

?>

<div class="col-12 col-md-6">
    <div class="card has-image has-metadata is-horizontal shadow parent">
        <!-- Image -->
        <div class="image-wrapper hover-scale">
            <img src="<?= $imgUrl ?>"
                 title="<?= Html::encode($model->title) ?>"
                 alt="<?= Html::encode($model->title) ?>"
                 class="image"/>
        </div>
        <!-- Body -->
        <div class="card-body bg-color white">
            <h3 class="title text-size-0 black"><?= $model->title ?></h3>
            <hr class="gray-25">
            <!-- Tag Cloud -->
            <div class="tag-cloud ">
                <?php if (isset($model->taxonomy->name)): ?>
                    <span class="badge outline gray-50 primary-hover">
                    <span class="badge-text gray white-hover"><?= $model->taxonomy->name ?></span>
                </span>
                <?php endif; ?>
                <?php if (!empty($model->age_limit)): ?>
                    <span class="badge outline gray-50 primary-hover">
                    <span class="badge-text gray white-hover"><?= $model->age_limit ?></span>
                </span>
                <?php endif; ?>
            </div>
        </div>
        <!-- Link -->
        <a href="<?= $url ?>" class="full-link"></a>
    </div>
</div>
