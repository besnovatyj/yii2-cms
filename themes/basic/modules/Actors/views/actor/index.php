<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Actors\entities\Taxonomy;
use yii\data\DataProviderInterface;
use yii\web\View;

/* @var $this View */
/* @var $dataProvider DataProviderInterface */
/* @var $taxonomy Taxonomy */

$this->title = 'Актёры';
$this->params['layoutTitle'] = $this->title;
$this->context->layout = '@themes/basic/layouts/actors/main';

$this->params['og:title'] = $this->title;
$this->params['og:image'] = $this->theme->getUrl('img/logo.jpg');

$this->params['breadcrumbs'][] = $this->title;

$this->registerMetaTag(['name' => 'keywords', 'content' => \Yii::$app->getModule('Config')->params['frontend']['app']['keywords']]);
$this->registerMetaTag(['name' => 'description', 'content' => \Yii::$app->getModule('Config')->params['frontend']['app']['description']]);
$this->registerMetaTag(['name' => 'author', 'content' => \Yii::$app->getModule('Config')->params['frontend']['app']['name']]);

?>

<section class="shock-section mt-3 mb-5">
    <div class="gallery stretched has-gap">
        <div class="bricklayer" data-columns="3">
            <?php foreach ($dataProvider->getModels() as $model): ?>
                <?= $this->render('_item', [
                    'model' => $model
                ]) ?>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="mt-4 text-center">
        <?= $this->render("_pagination", ['dataProvider' => $dataProvider]) ?>
    </div>
</section>
