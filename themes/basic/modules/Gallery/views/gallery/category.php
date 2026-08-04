<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use modules\gallery\entities\Category;
use yii\data\DataProviderInterface;
use yii\web\View;

/* @var $this View */
/* @var $dataProvider DataProviderInterface */
/* @var $category Category */

$this->title = $category->name;
$this->context->layout = '';

$this->params['og:title'] = $this->title;
$this->params['og:image'] = $this->theme->getUrl('img/logo.jpg');

$this->params['breadcrumbs'][] = ['label' => 'Галерея', 'url' => ['index']];
foreach ($category->parents as $parent) {
    if (!$parent->isRoot()) {
        $this->params['breadcrumbs'][] = ['label' => $parent->name, 'url' => ['category', 'id' => $parent->id]];
    }
}
$this->params['breadcrumbs'][] = $category->name;

$this->registerMetaTag(['name' => 'title', 'content' => $category->getSeoTitle()]);
$this->registerMetaTag(['name' => 'keywords', 'content' => $category->meta->keywords]);
$this->registerMetaTag(['name' => 'description', 'content' => $category->meta->description]);
$this->registerMetaTag(['name' => 'author', 'content' => \Yii::$app->getModule('Config')->params['frontend']['app']['name']]);

$this->params['active_category'] = $category; // Для виджета

?>
<section class="shock-section mt-3 mb-5">
    <?= $this->render('_subcategories', [
        'category' => $category
    ]) ?>
    <?php if (trim($category->description)): ?>
        <div class="panel panel-default">
            <div class="panel-body">
                <?= Yii::$app->formatter->asHtml($category->description) ?>
            </div>
        </div>
    <?php endif; ?>
    <?= $this->render('_list', [
        'dataProvider' => $dataProvider
    ]) ?>
</section>
