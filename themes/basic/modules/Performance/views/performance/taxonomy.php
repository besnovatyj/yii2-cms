<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Performance\entities\Taxonomy;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;
use yii\data\DataProviderInterface;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $dataProvider DataProviderInterface */
/* @var $taxonomy Taxonomy */

$this->title = $taxonomy->name;
$this->params['layoutTitle'] = $this->title;
$this->context->layout = 'main';

$this->params['og:title'] = $this->title;
$this->params['og:image'] = $this->theme->getUrl('img/logo.jpg');

$this->params['breadcrumbs'][] = ['label' => 'Все спектакли', 'url' => ['index']];

$this->params['breadcrumbs'] = new TreeQueryScope(Taxonomy::class)->breadcrumbs($taxonomy, urlCallback: function ($item) {
    return Url::to(['taxonomy', 'slug' => $item->slug]);
});

$this->registerMetaTag(['name' => 'keywords', 'content' => \Yii::$app->getModule('Config')->params['frontend']['app']['keywords']]);
$this->registerMetaTag(['name' => 'description', 'content' => \Yii::$app->getModule('Config')->params['frontend']['app']['description']]);
$this->registerMetaTag(['name' => 'author', 'content' => \Yii::$app->getModule('Config')->params['frontend']['app']['name']]);

$this->params['active_taxonomy'] = $taxonomy; // Для виджета

?>

<section class="shock-section mt-3 mb-5">
    <div class="row g-3">
        <?php foreach ($dataProvider->getModels() as $model): ?>
            <?= $this->render('_item', [
                'model' => $model,
            ]) ?>
        <?php endforeach; ?>
    </div>

    <div class="mt-4 text-center">
        <?= $this->render("_pagination", ['dataProvider' => $dataProvider]) ?>
    </div>
</section>
