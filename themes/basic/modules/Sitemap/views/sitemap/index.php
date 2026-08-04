<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use yii\helpers\Html;
use yii\web\View;

/** @var $this View */
/** @var $data array */

$this->title = 'Карта сайта';
$this->context->layout = '';

$this->params['og:title'] = $this->title;
$this->params['og:image'] = $this->theme->getUrl('img/logo.jpg');

$this->params['breadcrumbs'][] = $this->title;

$this->registerMetaTag(['name' => 'keywords', 'content' => \Yii::$app->getModule('Config')->params['frontend']['app']['keywords']]);
$this->registerMetaTag(['name' => 'description', 'content' => \Yii::$app->getModule('Config')->params['frontend']['app']['description']]);
$this->registerMetaTag(['name' => 'author', 'content' => \Yii::$app->getModule('Config')->params['frontend']['app']['name']]);

?>

<section class="shock-section mt-3 mb-5">
    <ul class="">
        <?php foreach ($data as $item): ?>
            <li>
                <a href="<?= $item['location'] ?>"><?= Html::encode($item['title']) ?></a>
            </li>
            <?php if (isset($item['subitems'])): ?>
                <ul>
                    <?php foreach ($item['subitems'] as $subItem): ?>
                        <li class="">
                            <a href=" <?= $subItem['location'] ?>"><?= Html::encode($subItem['title']) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <br/>
        <?php endforeach; ?>
    </ul>
</section>
