<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\DataProviderInterface */

/* @var $tag \modules\gallery\entities\Tag */

use yii\helpers\Html;

$this->title = 'Галереи с тегом: ' . $tag->name;
$this->context->layout = '';

$this->params['og:title'] = $this->title;
$this->params['og:image'] = $this->theme->getUrl('img/logo.jpg');

$this->params['breadcrumbs'][] = ['label' => 'Галерея', 'url' => ['index']];
$this->params['breadcrumbs'][] = $tag->name;

$this->registerMetaTag(['name' => 'keywords', 'content' => \Yii::$app->getModule('Config')->params['frontend']['app']['keywords']]);
$this->registerMetaTag(['name' => 'description', 'content' => \Yii::$app->getModule('Config')->params['frontend']['app']['description']]);
$this->registerMetaTag(['name' => 'author', 'content' => \Yii::$app->getModule('Config')->params['frontend']['app']['name']]);
?>

<section class="shock-section mt-3 mb-5">
    <h1>Галереи с тегом: &laquo;<?= Html::encode($tag->name) ?>&raquo;</h1>
    <hr/>
    <?= $this->render('_list', [
        'dataProvider' => $dataProvider
    ]) ?>
</section>
