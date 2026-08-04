<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use yii\data\DataProviderInterface;
use yii\web\View;

/* @var $this View */
/* @var $dataProvider DataProviderInterface */

?>

<?= \yii\bootstrap5\LinkPager::widget([
    'pagination' => $dataProvider->getPagination(),
//            'firstPageLabel' => false,
//            'lastPageLabel' => false,
//            'prevPageLabel' => false,
//            'nextPageLabel' => false,
    'options' => [
//                'tag' => 'ul',
        'class' => 'pagination',
//                'id' => '',
    ],
    'linkOptions' => ['class' => 'page-link'],
//            'activePageCssClass' => '',
]) ?>
