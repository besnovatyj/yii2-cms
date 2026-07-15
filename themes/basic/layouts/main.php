<?php

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

    <!-- Favicon and Touch Icons (https://ru.rakko.tools/tools/69/) некоторых иконок нет в этих ссылках, но есть в директории, это для Android -->
    <link rel="apple-touch-icon" sizes="57x57" href="/basic/favicon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/basic/favicon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/basic/favicon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/basic/favicon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/basic/favicon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/basic/favicon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/basic/favicon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/basic/favicon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/basic/favicon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/basic/favicon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/basic/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/basic/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/basic/favicon/favicon-16x16.png">
    <link rel="manifest" href="/basic/favicon/manifest.json">
    <link rel="shortcut icon" href="/basic/favicon/favicon.ico">
    <link rel="mask-icon" href="/basic/favicon/safari-pinned-tab.svg" color="#ffffff">
    <meta name="apple-mobile-web-app-title" content="BasicTheme">
    <meta name="application-name" content="BasicTheme">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
    <meta name="msapplication-config" content="/basic/favicon/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">

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

    <?php ThemeAssets::register($this) ?>
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
