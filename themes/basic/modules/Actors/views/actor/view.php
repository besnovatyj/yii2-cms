<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Actors\entities\actors\Actor;
use Besnovatyj\Actors\entities\Taxonomy;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $actor Actor */

$this->title = $actor->name;
$this->params['layoutTitle'] = $this->title;
$this->context->layout = '@themes/basic/layouts/actors/view';
$this->params['og:title'] = $this->title;
$this->params['og:image'] = $this->theme->getUrl('img/logo.jpg');

$this->registerMetaTag(['name' => 'title', 'content' => $actor->getSeoTitle()]);
$this->registerMetaTag(['name' => 'keywords', 'content' => $actor->meta->keywords]);
$this->registerMetaTag(['name' => 'description', 'content' => $actor->meta->description]);
$this->registerMetaTag(['name' => 'author', 'content' => \Yii::$app->getModule('Config')->params['frontend']['app']['name']]);

$this->params['breadcrumbs'][] = ['label' => 'Актёр', 'url' => ['index']];

$treeScope = new \Besnovatyj\TreeManager\Manager\TreeQueryScope(Taxonomy::class);
foreach ($treeScope->parentsQuery($actor->taxonomy)->all() as $parent) {
    if ((int)$parent->depth > 0) {
        $this->params['breadcrumbs'][] = ['label' => $parent->name, 'url' => ['taxonomy', 'slug' => $parent->slug]];
    }
}

$this->params['breadcrumbs'][] = ['label' => $actor->taxonomy->name, 'url' => ['taxonomy', 'slug' => $actor->taxonomy->slug]];
$this->params['breadcrumbs'][] = $actor->name;

$this->params['active_taxonomy'] = $actor->taxonomy; // Для виджета

?>

<section class="shock-section mt-3 mb-5">
    <div class="container">
        <div class="row">
            <div class="col-3">
                <h3>Фото</h3>
                <?php if (is_array($actor->images)): ?>
                    <?php foreach ($actor->images as $i => $image): ?>
                        <?php if ($i === 0): ?>
                            <a href="<?= $image->getUploadUrl('file') ?>" class="item lightbox-link hover-zoom">
                                <div class="text-wrapper text-center">
                                    <h3 class="title text-size-0 white text-shadowed"><?= Html::encode($actor->name) ?></h3>
                                </div>
                                <div class="image-wrapper shadow rounded">
                                    <div class="overlay tertiary-25"></div>
                                    <img src="<?= $image->getThumbUrl('file', 'thumb') ?>" class="image"
                                         alt="<?= Html::encode($actor->name) ?>"/>
                                </div>
                            </a>
                        <?php else: ?>
                            <?php $this->beginBlock('actor_all_images') ?>
                            <div>
                                <a href="<?= $image->getUploadUrl('file') ?>" data-lightbox="gallery-poster">
                                    <img src="<?= $image->getThumbUrl('file', 'thumb') ?>"
                                         alt="<?= Html::encode($actor->name) ?>"/>
                                </a>
                            </div>
                            <?php $this->endBlock() ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="col-3">
                <h3>Описание</h3>
                <?= Yii::$app->formatter->asHtml($actor->description, [
                    'Attr.AllowedRel' => ['nofollow'],
                    'HTML.SafeObject' => true,
                    'HTML.SafeIframe' => true,
                    'URI.SafeIframeRegexp' => '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%',
                ]) ?>
            </div>
            <div class="col-3">
                <h3>Теги</h3>
                <?php foreach ($actor->tags as $tag): ?>
                    <a href="<?php echo Html::encode(Url::to(['tag', 'id' => $tag->id])) ?>">
                        <?php echo Html::encode($tag->name) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
