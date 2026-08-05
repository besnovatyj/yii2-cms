<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Blog\entities\taxonomy\Taxonomy;
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

//$this->params['breadcrumbs'][] = ['label' => '1212', 'url' => ['index']];
$this->params['breadcrumbs'] = new TreeQueryScope(Taxonomy::class)->breadcrumbs($taxonomy, urlCallback: function ($item) use ($taxonomy) {
    if ($item->id !== $taxonomy->id) {
        return Url::to(['taxonomy', 'slug' => $item->slug]);
    }
    return false;
});

$this->registerMetaTag(['name' => 'title', 'content' => $taxonomy->getSeoTitle()]);
$this->registerMetaTag(['name' => 'keywords', 'content' => $taxonomy->meta->keywords]);
$this->registerMetaTag(['name' => 'description', 'content' => $taxonomy->meta->description]);
$this->registerMetaTag(['name' => 'author', 'content' => Yii::$app->getModule('Config')->params['frontend']['app']['name']]);

$this->params['active_taxonomy'] = $taxonomy;
?>

<section class="container mt-3 mb-5">
    <?php if ($taxonomy->description): ?>
        <div class="basic-intro mb-2 text-center">
            <h2 class="title gray-50 text-style-5">
                <?= Yii::$app->formatter->asHtml($taxonomy->description) ?>
            </h2>
            <hr class="gray-25">
        </div>
    <?php endif; ?>
    <!-- Posts -->
    <div class="row g-4" data-masonry='{"percentPosition": true }'>
        <?php foreach ($dataProvider->getModels() as $model): ?>
            <?= $this->render('_post', [
                'model' => $model,
            ]) ?>
        <?php endforeach; ?>
    </div>
    <div class="mt-4 text-center">
        <?= $this->render("_pagination", ['dataProvider' => $dataProvider]) ?>
    </div>
</section>
