<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use modules\menu\widgets\FrontendNav\FrontendNav;

?>
<div class="container bg-secondary">
    <div class="d-flex flex-row justify-content-start">
        <div class="p-1">
            <a href="<?= \yii\helpers\Url::home() ?>" class="d-block w-100 h-100 p-2 bg-info rounded-5">Logo</a>
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
