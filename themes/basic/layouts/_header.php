<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Menu\widgets\FrontendNav\FrontendNav;
use yii\bootstrap5\Breadcrumbs;
use yii\helpers\Url;

?>
<div class="container bg-secondary">
    <div class="d-flex flex-row justify-content-start">
        <div class="p-1">
            <a href="<?= Url::home() ?>" class="d-block w-100 h-100 p-2 bg-info rounded-5">Logo</a>
        </div>
        <div>
            <?= FrontendNav::widget([
                'slug' => 'main', // Отображение меню с slug='main'
                'options' => ['class' => 'navbar-nav ms-auto'],
            ]);
            ?>
        </div>
    </div>
</div>
<div class="container bg-secondary">
    <div class="breadcrumb">
        <?= Breadcrumbs::widget(
            [
                'options' => ['class' => 'lh-sm', 'aria-label' => 'breadcrumb'],
                'links' => $this->params['breadcrumbs'] ?? [],
            ]
        ) ?>
    </div>
</div>
