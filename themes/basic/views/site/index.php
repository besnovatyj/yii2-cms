<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use yii\web\View;

/* @var $this View */

?>
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="p-3 bg-warning blue-100">
                <h2>Из темы</h2>
                <div>Тема:</div>
                <div>
                    Название: <?= $this->theme->name; ?>
                </div>
                <div>
                    Базовая директория: <?= $this->theme->basePath; ?>
                </div>
            </div>
        </div>
    </div>
</div>
