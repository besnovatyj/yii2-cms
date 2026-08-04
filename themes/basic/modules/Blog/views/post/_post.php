<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model modules\blog\entities\Post */

$url = Url::to(['view', 'id' => $model->id]);

$emptyPostUrl = Url::to(['/static_assets_bd/images/blog/empty-post.jpg'], true);
?>

<div class="col-12 col-lg-6">
    <div class="card has-image has-metadata shadow parent">
        <!-- Icon -->
        <?php if ($model->isPinned()): ?>
            <div class="sticky-post-icon secondary">
                <i class="fas fa-thumbtack icon tertiary"></i>
            </div>
        <?php endif; ?>
        <!-- Label -->
        <span class="label-vertical to-bottom-right-out">
          <span class="label-line gray"></span>
          <span class="label-text gray">
            <!--<i class="icon fa-regular fa-clock"></i>-->
          </span>
        </span>
        <!-- Image -->
        <div class="image-wrapper rounded-top hover-zoom">
            <img src="<?= Html::encode($model->getThumbFileUrl('photo', 'blog_list', $emptyPostUrl)) ?>"
                 alt="<?= Html::encode($model->title) ?>" class="image"/>
        </div>
        <!-- Body -->
        <div class="card-body rounded-bottom bg-color white">
            <h3 class="title text-size-0 black"><?= StringHelper::truncateWords($model->title, 8, '...', true) ?></h3>
            <div class="description line-clamp-3 limiter">
                <?= StringHelper::truncate($model->description, 100) ?>
                <div class="bottom"></div>
            </div>
            <hr class="gray-25">
            <!-- Tag Cloud -->
            <div class="tag-cloud">
                <?php if ($model->comments_count > 0): ?>
                    <span class="badge outline gray-50 primary-hover">
                        <span class="badge-text gray white-hover">
                        Комментариев: <?php Html::encode($model->comments_count) ?>
                        </span>
                    </span>
                <?php endif; ?>

                <?php // if (count($model->tags)): ?>
                <?php // foreach ($model->tags as $tag): ?>
                <!--<span class="badge outline gray-50 primary-hover">-->
                <!--<span class="badge-text gray white-hover">-->
                <?php // echo Html::encode($tag->name) ?>
                <!--</span>-->
                <!--</span>-->
                <?php // endforeach; ?>
                <?php // endif; ?>

                <span class="badge outline gray-50 primary-hover">
                    <span class="badge-text gray white-hover">
                        <?php echo Yii::$app->formatter->asRelativeTime($model->created_at) ?>
                    </span>
                </span>
            </div>
        </div>
        <!-- Link -->
        <a href="<?= Html::encode($url) ?>" class="full-link"></a>
    </div>
</div>
