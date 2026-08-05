<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Page\entities\Page;
use Besnovatyj\Shortcode\widgets\ShortcodeContent;
use yii\web\View;

/** @var $page Page */
/** @var $this View */

$this->title = $page->title;
$this->params['layoutTitle'] = $this->title;
$this->context->layout = 'main';

$this->params['og:title'] = $this->title;
$this->params['og:image'] = $this->theme->getUrl('img/logo.jpg');
//
//$this->params['breadcrumbs'][] = ['label' => 'Документы', 'url' => ['index']];
//foreach ($page->parents as $parent) {
//    if (!$parent->isRoot()) {
//        $this->params['breadcrumbs'][] = ['label' => $parent->name, 'url' => ['category', 'id' => $parent->id]];
//    }
//}
$this->params['breadcrumbs'][] = $page->title;

$this->registerMetaTag(['name' => 'keywords', 'content' => $page->meta->keywords]);
$this->registerMetaTag(['name' => 'description', 'content' => $page->meta->description]);
$this->registerMetaTag(['name' => 'author', 'content' => \Yii::$app->getModule('Config')->params['frontend']['app']['name']]);
?>
<section class="shock-section mt-3 mb-5">
    <?= ShortcodeContent::widget([
        'content' => $page->content,
    ]);
    ?>
</section>
