<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Backend\Widgets\breadcrumbs\Breadcrumbs;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h5 me-2 fw-semibold"><?= Html::encode($this->title) ?></h1>

    <?= Breadcrumbs::widget(
        [
            'options' => ['class' => 'float-end ms-2 lh-sm', 'aria-label' => 'breadcrumb'],
            'links' => $this->params['breadcrumbs'] ?? [],
        ]
    ) ?>

</div>
