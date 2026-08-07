<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use yii\helpers\Html;
use yii\web\View;

/* @var $this View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

$this->title = $name;
$this->context->layout = '@themes/bootstrap/layouts/main';
?>
<section class="container my-4 my-lg-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-4">
                <p class="display-1 fw-bold text-secondary mb-0"><?= (int)$exception->getCode() ?></p>
                <h1 class="h3"><?= Html::encode($name) ?></h1>
            </div>

            <div class="alert alert-danger" role="alert">
                <?= nl2br(Html::encode($message)) ?>
            </div>

            <div class="text-center">
                <a href="<?= Yii::$app->homeUrl ?>" class="btn btn-primary">На главную</a>
            </div>
        </div>
    </div>
</section>
