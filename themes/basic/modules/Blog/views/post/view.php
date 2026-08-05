<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Blog\entities\Post;
use themes\berdramashock\widgets\comments\CommentsWidget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $post Post */

$this->title = $post->title;
$this->params['layoutTitle'] = $this->title;
$this->context->layout = 'main';

$this->params['og:title'] = $this->title;
$this->params['og:image'] = $this->theme->getUrl('img/logo.jpg');

$this->params['breadcrumbs'][] = ['label' => 'Блог', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $post->taxonomy->name, 'url' => ['taxonomy', 'slug' => $post->taxonomy->slug]];
$this->params['breadcrumbs'][] = $post->title;

$this->registerMetaTag(['name' => 'title', 'content' => $post->getSeoTitle()]);
$this->registerMetaTag(['name' => 'description', 'content' => $post->meta->description]);
$this->registerMetaTag(['name' => 'keywords', 'content' => $post->meta->keywords]);
$this->registerMetaTag(['name' => 'author', 'content' => Yii::$app->getModule('Config')->params['frontend']['app']['name']]);

$this->params['active_taxonomy'] = $post->taxonomy;

$emptyPostUrl = Url::to(['/static_assets_bd/images/blog/empty-post.jpg'], true);
?>

<section class="container mt-3 mb-5">
    <?php // echo $post->getImageFileUrl('photo', $emptyPostUrl) ?>
    <?php // echo Yii::$app->formatter->asDate($post->created_at, 'dd') ?>
    <?php // echo Yii::$app->formatter->asDate($post->created_at, 'MMM, yyyy') ?>
    <?php // echo Yii::$app->formatter->asDateTime($post->created_at, 'yyyy-MM-dd HH:mm') ?>
    <?php // echo Html::encode($post->title) ?>
    <?php // echo Html::encode($post->comments_count) ?>

    <?= CommentsWidget::widget([
        'post' => $post,
    ]) ?>

    <div class="content max-w-100 scheme-1">

        <!-- Image -->
        <figure class="figure">
            <img src="<?= $post->getThumbUrl('photo', 'blog_list', $emptyPostUrl) ?>" class="image shadow rounded"
                 alt="Image name">
            <figcaption class="figure-caption text-center"><?= $post->title ?></figcaption>
        </figure>

        <?= $post->content ?>

        <!-- Tag Cloud -->
        <div class="block-section">
            <h2>Tags</h2>
            <div class="tag-cloud">
                <?php if (count($post->tags)): ?>
                    <?php foreach ($post->tags as $tag): ?>
                        <a href="<?= Url::to(['tag', 'slug' => $tag->slug]) ?>" class="link">
                          <span class="badge text-bg-secondary">
                                <?= Html::encode($tag->name) ?>
                          </span>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
