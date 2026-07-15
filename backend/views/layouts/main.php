<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use backend\views\assets\AdmAssets;
use yii\base\InvalidConfigException;
use yii\web\AssetBundle;
use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $content string */

AdmAssets::register($this);
$assetUrl = '';
try {
    \yii\web\YiiAsset::register($this);
    $admBundle = Yii::$app->assetManager->getBundle(AdmAssets::class);
    if ($admBundle instanceof AssetBundle) {
        $assetUrl = $admBundle->baseUrl;
    } else {
        throw new InvalidConfigException('The assetBundle class must be a valid asset bundle.');
    }
    if (empty($assetUrl)) {
        throw new InvalidConfigException('The base assets directory cannot be accessed from the Web.');
    }
} catch (InvalidConfigException $e) {
    Yii::$app->errorHandler->logException($e);
    echo $e->getMessage();
}

try {
    echo \Besnovatyj\Alert\Widget::widget();
} catch (Throwable $e) {
    Yii::$app->errorHandler->logException($e);
    echo $e->getMessage();
}

?>

<?php $this->beginPage() ?>
<!doctype html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <?= Html::csrfMetaTags() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <title><?= Html::encode($this->title) ?></title>
    <!-- Favicons -->
    <?php $this->registerLinkTag(['rel' => 'icon', 'type' => 'image/png', 'sizes' => '96x96', 'href' => $assetUrl . '/favicon/favicon-96x96.png']); ?>
    <?php $this->registerLinkTag(['rel' => 'icon', 'type' => 'image/svg+xml', 'href' => $assetUrl . '/favicon/favicon.svg']); ?>
    <?php $this->registerLinkTag(['rel' => 'shortcut icon', 'href' => $assetUrl . '/favicon/favicon.ico']); ?>
    <?php $this->registerLinkTag(['rel' => 'apple-touch-icon', 'sizes' => '180x180', 'href' => $assetUrl . '/favicon/apple-touch-icon.png']); ?>
    <?php $this->registerMetaTag(['name' => 'apple-mobile-web-app-title', 'content' => 'Admin panel'], 'apple-mobile-web-app-title'); ?>
    <?php $this->registerLinkTag(['rel' => 'manifest', 'href' => $assetUrl . '/favicon/site.webmanifest']); ?>

    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<!-- Header -->
<?= $this->render('_header.php', ['assetUrl' => $assetUrl]) ?>
<div class="container-fluid">
    <div class="row">
        <!-- Left sidebar -->
        <div class="col-md-3 col-lg-2 sidebar p-0">
            <?= $this->render('_sidebar.php', ['assetUrl' => $assetUrl]) ?>
        </div>
        <!-- Main content -->
        <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4">
            <?= $this->render('_content_header.php', ['assetUrl' => $assetUrl]) ?>
            <?php echo $content ?>
            <footer class="d-flex flex-wrap justify-content-between align-items-center border-top mt-4 mb-2 px-2">
                <p class="col-md-4 mb-0 text-body-secondary"><?= 'Yii: ' . Yii::getVersion() ?></p>
            </footer>
        </main>
    </div>
</div>
<!-- Right sidebar -->
<?= $this->render('_sidebar_right.php', ['assetUrl' => $assetUrl]) ?>
<?= $this->render('_grid_filters.php', ['assetUrl' => $assetUrl]) ?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
