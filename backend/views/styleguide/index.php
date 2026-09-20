<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

use yii\helpers\Html;

/**
 * Витрина элементов интерфейса админки: все разделы на одной странице, чтобы было видно, что
 * элементы согласованы между собой. Заголовок страницы рисует layout (`_content_header.php`).
 *
 * @var yii\web\View $this
 * @var array<string, string> $chunks id чанка => подпись раздела
 */

$this->title = 'Элементы интерфейса';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <nav class="col-lg-3 order-lg-2 mb-4" aria-label="Разделы витрины">
        <div class="sticky-lg-top" style="top: 1rem">
            <div class="list-group list-group-flush small">
                <?php foreach ($chunks as $id => $title): ?>
                    <a class="list-group-item list-group-item-action"
                       href="#chunk-<?= Html::encode($id) ?>"><?= Html::encode($title) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </nav>

    <div class="col-lg-9 order-lg-1">
        <p class="text-body-secondary">
            Демонстрация в реальном окружении админки: тот же макет, та же тема, те же ассеты.
            Исходная разметка раздела — в файле, указанном под его заголовком; оттуда её и копируем.
        </p>

        <?php if ($chunks === []): ?>
            <div class="alert alert-info">В <code>views/styleguide/chunks/</code> пока нет ни одного файла.</div>
        <?php endif; ?>

        <?php foreach ($chunks as $id => $title): ?>
            <section id="chunk-<?= Html::encode($id) ?>" class="mb-5">
                <div class="d-flex justify-content-between align-items-baseline flex-wrap gap-2 border-bottom mb-3">
                    <h2 class="h5 fw-semibold mb-1"><?= Html::encode($title) ?></h2>
                    <code class="small text-body-secondary">views/styleguide/chunks/<?= Html::encode($id) ?>.php</code>
                </div>
                <?= $this->render('chunks/' . $id) ?>
            </section>
        <?php endforeach; ?>
    </div>
</div>
