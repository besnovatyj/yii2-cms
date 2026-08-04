<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use modules\gallery\entities\Gallery\Gallery;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $gallery Gallery */

$this->title = $gallery->name;
$this->context->layout = 'main';

$this->params['og:title'] = $this->title;
$this->params['og:image'] = $this->theme->getUrl('img/logo.jpg');

$this->params['breadcrumbs'][] = ['label' => 'Галерея', 'url' => ['index']];
foreach ($gallery->category->parents as $parent) {
    if (!$parent->isRoot()) {
        $this->params['breadcrumbs'][] = ['label' => $parent->name, 'url' => ['category', 'id' => $parent->id]];
    }
}
$this->params['breadcrumbs'][] = ['label' => $gallery->category->name, 'url' => ['category', 'id' => $gallery->category->id]];
$this->params['breadcrumbs'][] = $gallery->name;

$this->registerMetaTag(['name' => 'title', 'content' => $gallery->getSeoTitle()]);
$this->registerMetaTag(['name' => 'keywords', 'content' => $gallery->meta->keywords]);
$this->registerMetaTag(['name' => 'description', 'content' => $gallery->meta->description]);
$this->registerMetaTag(['name' => 'author', 'content' => Yii::$app->getModule('Config')->params['frontend']['app']['name']]);

$this->params['active_category'] = $gallery->category; // Для виджета

?>

<section class="shock-section mt-3 mb-5">
    <div class="m-t-50">
        <div class="float-l m-r-30 m-b-25">
            <?php foreach ($gallery->images as $i => $image): ?>
                <?php if ($i == 0): ?>
                    <div>
                        <a href="<?= $image->getImageFileUrl('file') ?>" data-lightbox="gallery-poster">
                            <img src="<?= $image->getThumbFileUrl('file', 'frontend_list') ?>"
                                 alt="<?= Html::encode($gallery->name) ?>"/>
                        </a>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <div>
            <!--        <div>-->
            <!--            Теги:-->
            <!--            --><?php //foreach ($gallery->tags as $tag): ?>
            <!--                <a href="--><?php //echo Html::encode(Url::to(['tag', 'id' => $tag->id])) ?><!--">-->
            <?php //echo Html::encode($tag->name) ?><!--</a>-->
            <!--            --><?php //endforeach; ?>
            <!--        </div>-->
            <div>
                <div class="m-b-25">
                    <?= Yii::$app->formatter->asHtml($gallery->description) ?>
                </div>
                <div class="col-sm-6 col-md-8 float-r">
                    <div class="flex-w">
                        <?php foreach ($gallery->images as $i => $image): ?>
                            <?php if ($i !== 0): ?>
                                <a class="item-gallery-section wrap-pic-w" href="<?= $image->getImageFileUrl('file') ?>"
                                   data-lightbox="gallery-item">
                                    <img src="<?= $image->getThumbFileUrl('file', 'gallery') ?>"
                                         alt=""/>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
