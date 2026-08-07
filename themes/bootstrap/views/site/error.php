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
?>
<?php

?>
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="p-3 bg-warning blue-100">
                <div class="border-1 m-1 p-3 rounded bg-white">
                    <div> Error name:</div>
                    <div><?= nl2br(Html::encode($name)) ?></div>
                </div>
                <div class="border-1 m-1 p-3 rounded bg-white">
                    <div>Error message:</div>
                    <div><?= nl2br(Html::encode($message)) ?></div>
                </div>
                <div class="border-1 m-1 p-3 rounded bg-white">
                    <div>Error code:</div>
                    <div><?= $exception->getCode() ?></div>
                </div>
                <div class="center">
                    <a href="<?= Yii::$app->homeUrl ?>" class="d-block p-3 m-3 rounded-2 text-white text-center bg-primary">Go home</a>
                </div>
            </div>
        </div>
    </div>
</div>
