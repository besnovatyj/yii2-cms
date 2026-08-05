<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Performance\entities\performance\Performance;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $model Performance */

$url = Url::to(['/performance/view/' . $model->id]);
$emptyPerformancePosterUrl = Url::to(['/static_assets_bd/images/performances/empty-performance.svg'], true);
$imgUrl = isset($model->mainImage) ? $model->mainImage->getThumbUrl('file', 'frontend_list', $emptyPerformancePosterUrl) : $emptyPerformancePosterUrl;

?>

<div class="card">
    <img src="<?= $imgUrl ?>" class="card-img-top" alt="<?= Html::encode($model->title) ?>"
         title="<?= Html::encode($model->title) ?>">
    <div class="card-body">
        <h5 class="card-title"><?= $model->title ?></h5>
        <p class="card-text">the card’s content.</p>
        <div class="tag-cloud">
            <?php if (isset($model->taxonomy->name)): ?>
                <span class="badge text-bg-secondary">
                    <?= $model->taxonomy->name ?>
                </span>
            <?php endif; ?>
            <?php if (!empty($model->age_limit)): ?>
                <span class="badge text-bg-secondary">
                    <?= $model->age_limit ?>
                </span>
            <?php endif; ?>
        </div>
        <hr>
        <a href="<?= $url ?>" class="btn btn-primary">Go</a>
    </div>
</div>
