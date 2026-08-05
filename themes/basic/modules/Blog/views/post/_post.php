<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Blog\entities\Post;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\web\View;

/* @var $this View */
/* @var $model Post */

$url = Url::to(['view', 'id' => $model->id]);

$emptyPostUrl = Url::to(['/static_assets_bd/images/blog/empty-post.jpg'], true);
?>

<div class="col-12 col-md-4">
    <div class="card">
        <img src="<?= Html::encode($model->getThumbUrl('photo', 'blog_list', $emptyPostUrl)) ?>"
             class="card-img-top" alt="<?= Html::encode($model->title) ?>"
             title="<?= Html::encode($model->title) ?>">
        <div class="card-body">
            <h5 class="card-title"><?= StringHelper::truncateWords($model->title, 8, '...', true) ?></h5>
            <p class="card-text"><?= StringHelper::truncate($model->description, 100) ?></p>
            <div class="tag-cloud">
                <?php if ($model->isPinned()): ?>
                    <div class="sticky-post-icon secondary">
                        <i class="fas fa-thumbtack icon tertiary"></i>
                    </div>
                <?php endif; ?>
                <div>
                    <?php if ($model->comments_count > 0): ?>
                        <span class="badge outline gray-50 primary-hover">
                        <span class="badge-text gray white-hover">
                        Комментариев: <?php Html::encode($model->comments_count) ?>
                        </span>
                    </span>
                    <?php endif; ?>
                </div>
                <div>
                    <?php echo Yii::$app->formatter->asRelativeTime($model->created_at, time()) ?>
                </div>
                <?php if (isset($model->taxonomy->name)): ?>
                    <a href="<?= Url::to(['taxonomy', 'slug' => $model->taxonomy->slug]) ?>" class="link">
                    <span class="badge text-bg-info">
                        <?= $model->taxonomy->name ?>
                    </span>
                    </a>
                <?php endif; ?>

                <div class="tag-cloud">
                    <?php if (count($model->tags)): ?>
                        <?php foreach ($model->tags as $tag): ?>
                            <a href="<?= Url::to(['tag', 'slug' => $tag->slug]) ?>" class="link">
                            <span class="badge text-bg-secondary">
                                <?php echo Html::encode($tag->name) ?>
                            </span>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <hr>
            <a href="<?= Html::encode($url) ?>" class="btn btn-primary">Go</a>
        </div>
    </div>
</div>
