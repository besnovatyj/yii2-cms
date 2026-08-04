<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\DataProviderInterface */
?>

    <div class="section-gallery p-t-118 p-b-100">
        <div class="wrap-gallery isotope-grid flex-w p-l-25 p-r-25">
            <?php foreach ($dataProvider->getModels() as $model): ?>
                <?= $this->render('_product', [
                    'model' => $model
                ]) ?>
            <?php endforeach; ?>
        </div>
    </div>

<?= \yii\widgets\LinkPager::widget([
    'pagination' => $dataProvider->getPagination(),
    'firstPageLabel' => false,
    'lastPageLabel' => false,
    'prevPageLabel' => false,
    'nextPageLabel' => false,
    'options' => [
        'tag' => 'div',
        'class' => 'pagination flex-c-m flex-w p-l-15 p-r-15 m-t-24 m-b-50',
        'id' => 'pager-container',
    ],
    'linkOptions' => ['class' => 'item-pagination flex-c-m trans-0-4'],
    'activePageCssClass' => 'active-pagination',
]) ?>
