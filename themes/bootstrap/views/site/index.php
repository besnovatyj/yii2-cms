<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use yii\web\View;

/* @var $this View */

$this->title = \Yii::$app->getModule('Config')->params['frontend']['app']['name'];
$this->context->layout = '@themes/bootstrap/layouts/main';

$this->params['og:title'] = $this->title;
$this->params['og:image'] = $this->theme->getUrl('img/logo.jpg');

$this->registerMetaTag(['name' => 'keywords', 'content' => \Yii::$app->getModule('Config')->params['frontend']['app']['keywords']]);
$this->registerMetaTag(['name' => 'description', 'content' => \Yii::$app->getModule('Config')->params['frontend']['app']['description']]);
$this->registerMetaTag(['name' => 'author', 'content' => \Yii::$app->getModule('Config')->params['frontend']['app']['name']]);


?>
<section class="container my-4 my-lg-5">
    <div class="p-4 p-md-5 mb-4 rounded-3 bg-body-tertiary border">
        <h1 class="display-6 mb-2"><?= \yii\helpers\Html::encode($this->title) ?></h1>
        <p class="text-muted mb-0">Базовая тема Bootstrap 5 (фолбэк).</p>
    </div>

    <div class="card">
        <div class="card-header">Из темы</div>
        <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between flex-wrap gap-2">
                <span class="text-muted">Название</span>
                <span><?= \yii\helpers\Html::encode($this->theme->name) ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between flex-wrap gap-2">
                <span class="text-muted">Базовая директория</span>
                <code><?= \yii\helpers\Html::encode($this->theme->basePath) ?></code>
            </li>
        </ul>
    </div>
</section>
