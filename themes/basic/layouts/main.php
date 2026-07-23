<?php

declare(strict_types=1);

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/* @var $this View
 * @var $content string
 */

use themes\basic\assets\ThemeAssets;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

?>
<?php ThemeAssets::register($this) ?>
<?php $this->beginPage() ?>
<!DOCTYPE HTML>
<html lang="<?= Yii::$app->language ?>" prefix="og: //ogp.me/ns#">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <?= Html::csrfMetaTags() ?>

    <!-- Viewport -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Canonical -->
    <link href="<?= Html::encode(Url::canonical()) ?>" rel="canonical">

    <!-- Identity -->
    <title><?= Html::encode($this->title) ?></title>

    <!-- Favicon and Touch Icons -->
    <?php
    // Реальный минимум, покрывающий 100% живых потребителей (по evilmartians и профильным гайдам)
    // порядок тегов важен, браузер берёт первый понятный.
    //  - icon.svg — твой вектор, для современных браузеров (масштабируется как хочет потребитель, даже умеет light/dark через @media внутри SVG);
    //  - favicon-32x32.png — фолбэк для Safari и старья;
    //  - apple-touch-icon.png 180×180 — экран «Домой» на iOS, обязан быть PNG (SVG Apple тут не ест), рекомендуют ~20px паддинга и непрозрачный фон.
    // ---
    // Про остальное:
    //  - .ico нужен по сути только тупым клиентам (RSS-читалки лезут строго в /favicon.ico) — тебе, по твоим словам, на них плевать, так что опционально;
    //  - manifest + icon-192/512.png + maskable — только если делаешь PWA. Для обычного сайта — не нужно, всю твою легаси-простыню (android/ms-иконки,
    //  manifest.json, browserconfig.xml) режем.
    //
    //  Итог по файлам: 3 штуки — icon.svg + favicon-32x32.png + apple-touch-icon.png. SVG как основной (твоё желание), два PNG закрывают Apple и старьё.
    ?>
    <link rel="icon" type="image/svg+xml" href="<?= $this->theme->getUrl('favicon/icon.svg') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= $this->theme->getUrl('favicon/favicon-32x32.png') ?>">
    <link rel="apple-touch-icon" href="<?= $this->theme->getUrl('favicon/apple-touch-icon.png') ?>">

    <!-- Open Graph tags -->
    <meta property="og:title" content='<?= $this->params['og:title'] ?? '' ?>'>
    <meta property="og:image" content="<?= $this->params['og:image'] ?? '' ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= Html::encode(Url::canonical()) ?>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap"
        rel="stylesheet">

    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<!-- Header -->
<?php echo $this->render("_header.php") ?>
<!-- Main -->
<main>
    <?= $content ?>
</main>
<!-- Footer -->
<?php echo $this->render("_footer.php") ?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
