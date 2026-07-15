<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

$this->context->layout = 'blank';
$this->title = $name;
?>

<style>
    .error-page {
        background-color: rgba(73, 73, 73, 0.29);
        -webkit-backdrop-filter: blur(9px);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(234, 234, 235, 0.2);
        padding: 80px 20px;
    }

    .error-page h1 {
        font-size: 50px;
        line-height: 1;
        font-weight: 600;
        padding-bottom: 0.5em;
    }

</style>

<section class="p-0 mesh-gradient">
    <div class="container-fluid d-flex flex-column">
        <div class="row align-items-center justify-content-center" style="min-height: 80vh">
            <div class="col-md-9 col-lg-6 my-5">
                <div class="text-center error-page">
                    <h1 class="mb-0 text-primary-emphasis"><?= nl2br(Html::encode($name)) ?></h1>
                    <h2 class="mb-4 text-primary-emphasis"><?= nl2br(Html::encode($message)) ?></h2>
                    <p class="w-sm-80 mx-auto mb-4 text-primary-emphasis">
                        Code: <?= $exception->getCode() ?>
                    </p>
                    <div>
                        <a href="<?= Yii::$app->homeUrl ?>" class="btn btn-lg me-sm-2 mb-2 mb-sm-0 text-white" style="background-color: #15396c">
                            Go Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
