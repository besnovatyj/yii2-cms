<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use modules\actors\entities\Taxonomy;
use yii\data\DataProviderInterface;
use yii\web\View;

/* @var $this View */
/* @var $taxonomy Taxonomy */
/* @var $dataProvider DataProviderInterface */

$this->title = $taxonomy->name;
$this->params['layoutTitle'] = $this->title;
$this->context->layout = 'actors/main';

$this->params['og:title'] = $this->title;
$this->params['og:image'] = $this->theme->getUrl('img/logo.jpg');

$this->params['breadcrumbs'][] = ['label' => 'Актёры', 'url' => ['index']];
foreach ($taxonomy->parents as $parent) {
    if (!$parent->isRoot()) {
        $this->params['breadcrumbs'][] = ['label' => $parent->name, 'url' => ['taxonomy', 'slug' => $parent->slug]];
    }
}
$this->params['breadcrumbs'][] = $taxonomy->name;

$this->registerMetaTag(['name' => 'title', 'content' => $taxonomy->getSeoTitle()]);
$this->registerMetaTag(['name' => 'keywords', 'content' => $taxonomy->meta->keywords]);
$this->registerMetaTag(['name' => 'description', 'content' => $taxonomy->meta->description]);
$this->registerMetaTag(['name' => 'author', 'content' => \Yii::$app->getModule('Config')->params['frontend']['app']['name']]);

$this->params['active_taxonomy'] = $taxonomy; // Для виджета

?>

<section class="shock-section mt-3 mb-5">
    <?php if ($taxonomy->description): ?>
        <div class="basic-intro mb-2 text-center">
            <h2 class="title gray-50 text-style-5">
                <?= Yii::$app->formatter->asHtml($taxonomy->description) ?>
            </h2>
            <hr class="gray-25">
        </div>
    <?php endif; ?>

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
