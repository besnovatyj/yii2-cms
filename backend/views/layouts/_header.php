<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use yii\helpers\Url;

?>

<header class="navbar bg-dark flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6 text-white" href="<?= Url::home() ?>">Admin Panel</a>

    <!-- Mobile only -->
    <ul class="navbar-nav flex-row d-md-none">
        <!-- Кнопка для левого сайдбара (мобильная версия) -->
        <li class="nav-item text-nowrap">
            <button class="nav-link px-3 text-white" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false"
                    aria-label="Toggle navigation">
                <i class="bi bi-list"></i>
            </button>
        </li>
        <!-- Toggle pageTopActions -->
        <li class="nav-item text-nowrap">
            <button class="nav-link px-1 text-white" type="button" data-bs-toggle="collapse"
                    data-bs-target="#pageTopActions" aria-controls="pageTopActions" aria-expanded="false"
                    aria-label="Toggle pageTopActions">
                <i class="bi bi-caret-down-square"></i>
            </button>
        </li>
        <!-- Кнопка для правого сайдбара (мобильная версия) -->
        <li class="nav-item text-nowrap">
            <button class="nav-link px-3 text-white" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#rightSidebarMenu" aria-controls="rightSidebarMenu" aria-expanded="false"
                    aria-label="Toggle right sidebar">
                <i class="bi bi-caret-left-square"></i>
            </button>
        </li>
    </ul>

    <div class="d-flex d-flex-inline">
        <ul class="navbar-nav flex-row d-none d-md-flex">
            <li class="nav-item text-nowrap">
                <?= \yii\helpers\Html::a(
                    '<span class="btn btn-danger btn-sm" type="button"><i class="bi bi-door-open"></i></span>',
                    ['/user/backend/auth/logout'],
                    [
                        'class' => 'nav-link pe-1',
                        'data' => [
                            'confirm' => 'Вы уверенны что хотите выйти?',
                            'method' => 'post',
                        ],
                    ]) ?>
            </li>
            <?php if (YII_DEBUG): ?>
                <li class="nav-item text-nowrap">
                    <a href="<?= Url::to('/gii') ?>" class="nav-link px-1 text-white">
                        <span class="btn btn-secondary btn-sm" type="button"><i class="bi bi-hammer"></i></span>
                    </a>
                </li>
            <?php endif; ?>
            <li class="nav-item text-nowrap">
                <a class="nav-link px-1 text-white"
                   href="<?= Yii::$app->get('frontendUrlManager')->hostInfo ?>"
                   target="_blank">
                    <span class="btn btn-warning btn-sm" type="button"><i class="bi bi-house-up"></i></span>
                </a>
            </li>
            <!-- Кнопка для правого сайдбара (десктоп) -->
            <li class="nav-item text-nowrap">
                <button class="nav-link px-1 text-white" type="button" data-bs-toggle="offcanvas"
                        data-bs-target="#rightSidebarMenu" aria-controls="rightSidebarMenu"
                        aria-expanded="false" aria-label="Toggle right sidebar">
                    <span class="btn btn-info btn-sm" type="button">
                        <i class="bi bi-caret-left-square"></i>
                    </span>
                </button>
            </li>
            <!-- Кнопка открытия сайдбара сразу на вкладке настроек -->
            <li class="nav-item text-nowrap">
                <button class="nav-link px-1 text-white" type="button" data-bs-toggle="offcanvas"
                        data-bs-target="#rightSidebarMenu" data-sidebar-tab="settings"
                        aria-controls="rightSidebarMenu" aria-expanded="false"
                        aria-label="Open sidebar settings tab">
                    <span class="btn btn-info btn-sm" type="button">
                        <i class="bi bi-gear"></i>
                    </span>
                </button>
            </li>
        </ul>
    </div>

    <div id="pageTopActions" class="w-100 collapse">
        <div class="p-2 text-white">Page actions:</div>
        <div class="p-2">
            <?php if (isset($this->blocks['pageTopActions'])): ?>
                <?= $this->blocks['pageTopActions'] ?>
            <?php endif; ?>
        </div>
    </div>
</header>
