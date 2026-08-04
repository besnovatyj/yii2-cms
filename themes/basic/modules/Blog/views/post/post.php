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
$this->context->layout = 'blog/post';

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

<section class="shock-section mt-3 mb-5">
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
            <img src="<?= $post->getThumbFileUrl('photo', 'blog_post', $emptyPostUrl) ?>" class="image shadow rounded"
                 alt="Image name">
            <figcaption class="figure-caption text-center"><?= $post->title ?></figcaption>
        </figure>

        <?= $post->content ?>

        <!-- Gallery -->
        <div class="block-section">
            <div class="gallery stretched-side has-gap">
                <div class="bricklayer" data-columns="3">
                    <a href="/static_assets_bd/images/jpg/d-1.jpg" class="item lightbox-link">
                        <div class="image-wrapper shadow rounded hover-zoom-rotate">
                            <div class="overlay black-50"></div>
                            <img src="/static_assets_bd/images/jpg/d-1.jpg" class="image rounded shadow"
                                 alt="This is an example description for this item."/>
                        </div>
                    </a>
                    <a href="/static_assets_bd/images/jpg/d-2.jpg" class="item lightbox-link">
                        <div class="image-wrapper shadow rounded hover-zoom-rotate">
                            <div class="overlay black-50"></div>
                            <img src="/static_assets_bd/images/jpg/d-2.jpg" class="image rounded shadow"
                                 alt="This is an example description for this item."/>
                        </div>
                    </a>
                    <!-- Video -->
                    <a href="https://vimeo.com/222990241" class="item active lightbox-link">
                        <i class="fa-solid fa-circle-play gallery-icon white floating-item"></i>
                        <div class="image-wrapper shadow rounded hover-zoom-rotate">
                            <div class="overlay black-50"></div>
                            <img src="/static_assets_bd/images/jpg/d-3.jpg" class="image rounded shadow"
                                 alt="This is an example description for this item."/>
                        </div>
                    </a>
                    <a href="/static_assets_bd/images/jpg/d-4.jpg" class="item lightbox-link">
                        <div class="image-wrapper shadow rounded hover-zoom-rotate">
                            <div class="overlay black-50"></div>
                            <img src="/static_assets_bd/images/jpg/d-4.jpg" class="image rounded shadow"
                                 alt="This is an example description for this item."/>
                        </div>
                    </a>
                    <a href="/static_assets_bd/images/jpg/d-5.jpg" class="item lightbox-link">
                        <div class="image-wrapper shadow rounded hover-zoom-rotate">
                            <div class="overlay black-50"></div>
                            <img src="/static_assets_bd/images/jpg/d-5.jpg" class="image rounded shadow"
                                 alt="This is an example description for this item."/>
                        </div>
                    </a>
                    <a href="/static_assets_bd/images/jpg/d-6.jpg" class="item lightbox-link">
                        <div class="image-wrapper shadow rounded hover-zoom-rotate">
                            <div class="overlay black-50"></div>
                            <img src="/static_assets_bd/images/jpg/d-6.jpg" class="image rounded shadow"
                                 alt="This is an example description for this item."/>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Paragraph -->
        <h3>Can you already see design with a new look?</h3>
        <p>As we have seen, it is not a simple term, as it concerns a process, science, profession and even attributes
            of everything we know.</p>
        <p>No wonder you are surrounded by examples of design: the page of this blog, the decoration of your office, the
            model of your shirt and even the shape of your chair. That's why design is so important.</p>
        <p>It allows the world we imagine to become reality in the best possible way.</p>

        <!-- Post navigation-->
        <div class="block-section">
            <div class="post-navigation">
                <div class="post-navigation-buttons">
                    <!-- Button -->
                    <a href="#" class="arrow-button prev scheme-1 primary">
                        <span class="arrow">
                          <span class="item"></span>
                          <span class="item"></span>
                        </span>
                        <span class="line"></span>
                        <span class="text">PREV ARTICLE</span>
                    </a>
                    <!-- Button -->
                    <a href="#" class="arrow-button next scheme-1 primary">
                        <span class="arrow">
                          <span class="item"></span>
                          <span class="item"></span>
                        </span>
                        <span class="line"></span>
                        <span class="text">NEXT ARTICLE</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Divider -->
        <div class="block-section">
            <span class="zzz scheme-1 gray-75"></span>
        </div>

        <!-- Tag Cloud -->
        <div class="block-section">
            <h2>Tags</h2>
            <div class="tag-cloud">

                <?php if (count($post->tags)): ?>
                    <?php foreach ($post->tags as $tag): ?>
                        <a href="<?= Url::to(['tag', 'id' => $tag->id]) ?>" class="link">
                          <span class="badge outline gray-50 primary-hover">
                            <span class="badge-text gray white-hover">
                                <?= Html::encode($tag->name) ?>
                            </span>
                          </span>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
                <a href="#" class="link">
                      <span class="badge outline active gray-50 primary-hover floating-item-smooth">
                        <span class="badge-text gray white-hover">ПРИМЕР ПЛАВАЮЩЕГО</span>
                      </span>
                </a>
            </div>
        </div>

        <!-- Post reference -->
        <div class="block-section">
            <div class="post-reference" data-lax="inertia-top">
                <div class="post-reference-container">
                    <div class="image-wrapper">
                        <img src="/static_assets_bd/images/png/b-2.png" alt="Image name" class="image">
                    </div>
                    <div class="text-wrapper">
                        <h5 class="title">
                            <mark class="animated-underline primary-50">John Young</mark>
                        </h5>
                        <p class="description">Co-founder of the Company. The Wall Street Journal named him the biggest
                            influencer on the web.</p>
                        <cite title="Source Title">Author of the article "Why design matters to our lives".</cite>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comments -->
        <div class="comments">
            <h2>Comments <span class="text-outline">(16)</span></h2>
            <div class="comments-wrapper">
                <!-- Comment -->
                <div id="comment-1" class="comment">
                    <div class="comment-metadata">
                        <div class="comment-author">
                            <div class="author-photo">
                                <img src="/static_assets_bd/images/jpg/p-1.jpg" class="image shadow" alt="Image name">
                            </div>
                            <h5 class="author-name">Alice Johnson</h5>
                        </div>
                        <span class="comment-date">
                          <i class="icon fas fa-calendar-alt"></i>
                          <span class="text">14 days ago</span>
                        </span>
                    </div>
                    <div class="comment-content">
                        <p>Hello, this theme is really amazing! It has many features that make my job as a developer
                            much easier. I'm sure my clients will love to see the final result of this project.</p>
                    </div>
                    <div class="comment-action">
                        <!-- Button -->
                        <a href="#comment-1" class="button simple">
                            <span class="button-text black primary-hover">Reply comment</span>
                            <i class="fa-solid fa-arrow-right button-icon black primary-hover"></i>
                        </a>
                        <i class="fa-solid fa-heart like-icon active primary"></i>
                    </div>
                </div>
                <!-- Comment -->
                <div id="comment-2" class="comment">
                    <div class="comment-metadata">
                        <div class="comment-author">
                            <div class="author-photo">
                                <img src="/static_assets_bd/images/jpg/p-1.jpg" class="image shadow" alt="Image name">
                            </div>
                            <h5 class="author-name">Kaled Ahmad</h5>
                        </div>
                        <span class="comment-date">
                          <i class="icon fas fa-calendar-alt"></i>
                          <span class="text">17 days ago</span>
                        </span>
                    </div>
                    <div class="comment-content">
                        <p>مرحبًا ، هذا المظهر رائع حقًا! يحتوي على العديد من الميزات التي تجعل عملي كمطور أسهل بكثير.
                            أنا متأكد من أن عملائي سيحبون رؤية موقع الويب الذي أقوم ببنائه لهم.</p>
                    </div>
                    <div class="comment-action">
                        <!-- Button -->
                        <a href="#comment-2" class="button simple">
                            <span class="button-text black primary-hover">Reply comment</span>
                            <i class="fa-solid fa-arrow-right button-icon black primary-hover"></i>
                        </a>
                        <i class="fa-solid fa-heart like-icon primary"></i>
                    </div>
                </div>
                <!-- Comment -->
                <div id="comment-3" class="comment">
                    <div class="comment-metadata">
                        <div class="comment-author">
                            <div class="author-photo">
                                <img src="/static_assets_bd/images/jpg/p-1.jpg" class="image shadow" alt="Image name">
                            </div>
                            <h5 class="author-name">Laura Garcia</h5>
                        </div>
                        <span class="comment-date">
                          <i class="icon fas fa-calendar-alt"></i>
                          <span class="text">26 days ago</span>
                        </span>
                    </div>
                    <div class="comment-content">
                        <p>Hola, este tema es realmente increíble! Tiene muchas características que hacen que mi trabajo
                            como desarrollador sea mucho más fácil. Estoy seguro de que a mis clientes les encantará ver
                            el sitio web que estoy creando para ellos.</p>
                    </div>
                    <div class="comment-action">
                        <!-- Button -->
                        <a href="#comment-3" class="button simple">
                            <span class="button-text black primary-hover">Reply comment</span>
                            <i class="fa-solid fa-arrow-right button-icon black primary-hover"></i>
                        </a>
                        <i class="fa-solid fa-heart like-icon primary"></i>
                    </div>
                </div>
                <!-- Comment -->
                <div id="comment-4" class="comment">
                    <div class="comment-metadata">
                        <div class="comment-author">
                            <div class="author-photo">
                                <img src="/static_assets_bd/images/jpg/p-1.jpg" class="image shadow" alt="Image name">
                            </div>
                            <h5 class="author-name">Dimitri Smirnov</h5>
                        </div>
                        <span class="comment-date">
                          <i class="icon fas fa-calendar-alt"></i>
                          <span class="text">14 days ago</span>
                        </span>
                    </div>
                    <div class="comment-content">
                        <p>Привет, эта тема действительно потрясающая! Он имеет множество функций, которые значительно
                            облегчают мою работу как разработчика. Я уверен, что моим клиентам понравится конечный
                            результат этого проекта.</p>
                    </div>
                    <div class="comment-action">
                        <!-- Button -->
                        <a href="#comment-4" class="button simple">
                            <span class="button-text black primary-hover">Reply comment</span>
                            <i class="fa-solid fa-arrow-right button-icon black primary-hover"></i>
                        </a>
                        <i class="fa-solid fa-heart like-icon primary"></i>
                    </div>
                </div>
            </div>
            <!-- Form -->
            <div class="form-area scheme-1 primary">
                <h3>Leave a reply</h3>
                <p>To write a comment you need to <a href="#" target="_blank"><u>login</u></a> to your account.</p>
                <form class="form-fields needs-validation" novalidate="novalidate">
                    <div class="form-row row">
                        <div class="form-col col-12">
                            <textarea class="form-control rounded-3 form-textarea" id="InputCompactExampleMessage"
                                      rows="3" placeholder="Comment" required="required"></textarea>
                            <div class="invalid-feedback">Please type something to post a comment.</div>
                        </div>
                    </div>
                    <div class="form-row row">
                        <div class="form-col col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" required="required">
                                <label class="form-label form-check-label">
                                    I agree to the <a href="#your-link" class="link black primary-hover"><u>terms of
                                            use</u>.</a>
                                </label>
                                <div class="invalid-feedback">Please accept the terms to continue.</div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row row">
                        <div class="form-col col-12">
                            <!-- Button -->
                            <button class="button shadow rounded-3 black primary-hover button-collision">
                                <span class="button-text white white-hover">Post comment</span>
                                <i class="fa-solid fa-arrow-right button-icon white white-hover"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
