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
