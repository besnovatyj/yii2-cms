<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Performance\entities\performance\Performance;
use Besnovatyj\Performance\entities\Taxonomy;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $performance Performance */

$this->title = $performance->title;
$this->params['layoutTitle'] = $this->title;
$this->context->layout = 'performance/view';

$this->params['og:title'] = $this->title;
$this->params['og:image'] = $this->theme->getUrl('img/logo.jpg');

$this->params['breadcrumbs'][] = ['label' => 'Спектакли', 'url' => ['index']];

//foreach ($performance->taxonomy->parents as $parent) {
//    if (!$parent->isRoot()) {
//        $this->params['breadcrumbs'][] = ['label' => $parent->name, 'url' => ['taxonomy', 'slug' => $parent->slug]];
//    }
//}

$treeScope = new \Besnovatyj\TreeManager\Manager\TreeQueryScope(Taxonomy::class);
foreach ($treeScope->parentsQuery($performance->taxonomy)->all() as $parent) {
    if ((int)$parent->depth > 0) {
        $this->params['breadcrumbs'][] = ['label' => $parent->name, 'url' => ['taxonomy', 'slug' => $parent->slug]];
    }
}


$this->params['breadcrumbs'][] = ['label' => $performance->taxonomy->name, 'url' => ['taxonomy', 'slug' => $performance->taxonomy->slug]];
$this->params['breadcrumbs'][] = $performance->title;

$this->registerMetaTag(['name' => 'keywords', 'content' => \Yii::$app->getModule('Config')->params['frontend']['app']['keywords']]);
$this->registerMetaTag(['name' => 'description', 'content' => \Yii::$app->getModule('Config')->params['frontend']['app']['description']]);
$this->registerMetaTag(['name' => 'author', 'content' => \Yii::$app->getModule('Config')->params['frontend']['app']['name']]);

$this->params['active_taxonomy'] = $performance->taxonomy; // Для виджета
?>

<section class="shock-section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="holder pb-3">
                    <div>
                        <h2 class="title black">
                            <span class="text-1 text-style-5"><?= Html::encode($performance->title) ?></span>
                        </h2>
                    </div>
                    <hr class="gray-25">
                    <div>
                        <div class="age-rating float-end"><?= $performance->age_limit ?></div>
                        <div><?= $performance->author ?></div>
                        <div><?= $performance->genre ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="collapsible">
                    <div class="collapse-group">
                        <a href="#collapse-performance-annotation" class="collapse-toggle parent" aria-expanded="true" aria-controls="collapse-performance-annotation"
                           data-bs-toggle="collapse">
                            <div class="collapse-button shadow rounded-circle white">
                                <span class="arrow-button cross scheme-1 primary">
                                <span class="arrow">
                                  <span class="item"></span>
                                  <span class="item"></span>
                                </span>
                                <span class="line"></span>
                              </span>
                            </div>
                            <h3 class="title text-style-11">
                                Аннотация
                            </h3>
                        </a>
                        <div id="collapse-performance-annotation" class="collapse-content collapse show">
                            <?= Yii::$app->formatter->asHtml($performance->description, [
                                'Attr.AllowedRel' => ['nofollow'],
                                'HTML.SafeObject' => true,
                                'HTML.SafeIframe' => true,
                                'URI.SafeIframeRegexp' => '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%',
                            ]) ?>
                        </div>
                    </div>
                    <?php if (strlen($performance->actors) > 0): ?>
                        <div class="collapse-group">
                            <a href="#collapse-performance-actors" class="collapse-toggle parent" aria-expanded="false"
                               aria-controls="collapse-performance-actors" data-bs-toggle="collapse">
                                <div class="collapse-button shadow rounded-circle white">
                    <span class="arrow-button cross scheme-1 primary">
                    <span class="arrow">
                      <span class="item"></span>
                      <span class="item"></span>
                    </span>
                    <span class="line"></span>
                  </span>
                                </div>
                                <h3 class="title text-style-11">
                                    В ролях
                                </h3>
                            </a>
                            <div id="collapse-performance-actors" class="collapse-content collapse">
                                <?= $performance->actors ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (strlen($performance->production_group) > 0): ?>
                        <div class="collapse-group">
                            <a href="#collapse-performance-production-group" class="collapse-toggle parent" aria-expanded="false"
                               aria-controls="collapse-performance-production-group" data-bs-toggle="collapse">
                                <div class="collapse-button shadow rounded-circle white">
                    <span class="arrow-button cross scheme-1 primary">
                    <span class="arrow">
                      <span class="item"></span>
                      <span class="item"></span>
                    </span>
                    <span class="line"></span>
                  </span>
                                </div>
                                <h3 class="title text-style-11">
                                    Постановочная группа
                                </h3>
                            </a>
                            <div id="collapse-performance-production-group" class="collapse-content collapse">
                                <?= $performance->production_group ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</section>

<?php if (is_array($performance->images) && sizeof($performance->images) > 0): ?>
    <section class="shock-section pt-3 pb-3">
        <div class="container">
            <!-- Intro -->
            <div class="basic-intro mb-2 text-center">
                <h2 class="title gray-50 text-style-5">Фотографии</h2>
                <hr class="gray-25">
            </div>
            <!-- Gallery -->
            <div class="gallery stretched has-gap">
                <div class="bricklayer" data-columns="4">

                    <?php foreach ($performance->images as $i => $image): ?>
                        <?php if ($i === 0): ?>
                            <!-- Выносим постер в отдельный блок  -->
                            <?php $this->beginBlock('frontend_item') ?>
                            <a href="<?= $image->getUploadUrl('file') ?>">
                                <img src="<?= $image->getThumbUrl('file', 'thumb') ?>"
                                     alt="<?= Html::encode($performance->title) ?>"/>
                            </a>
                            <?php $this->endBlock() ?>
                        <?php else: ?>
                            <a href="<?= $image->getUploadUrl('file') ?>" class="item lightbox-link hover-zoom">
                                <div class="image-wrapper shadow rounded">
                                    <div class="overlay black-50"></div>
                                    <img src="<?= $image->getThumbUrl('file', 'thumb') ?>" class="image"
                                         alt="Фотография спектакля <?= $performance->title ?>."/>
                                </div>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>

                </div>
            </div>
        </div>
    </section>
<?php endif; ?>
















