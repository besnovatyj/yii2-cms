<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

use Besnovatyj\Backend\Widgets\nav\NavWidget;
use Besnovatyj\Kernel\security\MenuAccessFilter;
use Besnovatyj\Modman\menu\MenuProvider;
use common\config\ConfigFactory;

/**
 * Меню собирается в рантайме из группы `admin-menu` (yiisoft/config) с живыми `active`-замыканиями
 * ({@see MenuProvider}). Артефакты `menu-*.php` больше не пишутся — источник только группа admin-menu.
 */
$menuItems = static function (string $location): array {
    $factory = new ConfigFactory();
    return $factory->has('admin-menu')
        ? Yii::$container->get(MenuProvider::class)->forLocation($location, $factory->get('admin-menu'))
        : [];
};
?>
<!-- Right Sidebar -->
<div class="offcanvas offcanvas-end bg-body-tertiary" tabindex="-1" id="rightSidebarMenu"
     aria-labelledby="rightSidebarMenuLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="rightSidebarMenuLabel">Settings</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#rightSidebarMenu"
                aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column p-0 pt-lg-3 overflow-y-auto">
        <!-- Навигация вкладок -->
        <ul class="nav nav-tabs px-2" id="sidebarTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="actions-tab" data-bs-toggle="tab" href="#actions" role="tab"
                   aria-controls="actions" aria-selected="true">Actions</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="settings-tab" data-bs-toggle="tab" href="#settings" role="tab"
                   aria-controls="settings" aria-selected="false">Settings</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="account-tab" data-bs-toggle="tab" href="#account" role="tab"
                   aria-controls="account" aria-selected="false">Account</a>
            </li>
        </ul>

        <!-- Контент вкладок -->
        <div class="tab-content p-2" id="sidebarTabContent">
            <!-- Вкладка Actions -->
            <div class="tab-pane fade show active" id="actions" role="tabpanel" aria-labelledby="actions-tab">
                <div class="p-2">
                    <?php
                    if (isset($this->blocks['pageRight.Actions'])): ?>
                        <?= $this->blocks['pageRight.Actions'] ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Вкладка Settings -->
            <div class="tab-pane fade" id="settings" role="tabpanel" aria-labelledby="settings-tab">

                <div class="p-2">
                    <?php if (isset($this->blocks['pageRight.Settings'])): ?>
                        <?= $this->blocks['pageRight.Settings'] ?>
                    <?php endif; ?>
                </div>

                <?php
                $backend_items = MenuAccessFilter::filter($menuItems('right-sidebar'));
                echo NavWidget::widget([
                    'items' => $backend_items,
                    'options' => ['class' => 'list-unstyled ps-0'],
                ]) ?>
                <?= NavWidget::widget([
                    'items' => [
                        '<li class="border-top my-3"></li>',
                        '<h6 class="text-center">Demo menu items</h6>',
                        [
                            'label' => 'Single item',
                            'iconClass' => 'bi bi-1-square me-1',
                            'url' => "#",
                            'active' => static function () {
                                return str_contains(Yii::$app->request->url, 'single_item');
                            },
                        ],
                        [
                            'label' => 'Home admin panel',
                            'iconClass' => 'bi bi-house-fill',
                            'items' => [
                                [
                                    'label' => 'Overview',
                                    'iconClass' => 'bi bi-eye me-1',
                                    'url' => '#',
                                    'active' => static function () {
                                        return str_contains(Yii::$app->request->url, 'home-admin');
                                    },
                                ],
                                [
                                    'label' => 'Updates',
                                    'iconClass' => 'bi bi-arrow-up-square me-1',
                                    'url' => '#',
                                    'active' => static function () {
                                        return str_contains(Yii::$app->request->url, 'home-admin');
                                    },
                                ],
                                [
                                    'label' => 'Reports',
                                    'iconClass' => 'bi bi-capsule me-1',
                                    'url' => '#',
                                    'active' => static function () {
                                        return str_contains(Yii::$app->request->url, 'home-admin');
                                    },
                                ],
                            ],
                        ],
                        [
                            'label' => 'Dashboard',
                            'iconClass' => 'bi bi-speedometer2',
                            'items' => [
                                [
                                    'label' => 'Overview',
                                    'iconClass' => 'bi bi-eye me-1',
                                    'url' => '#',
                                    'active' => static function () {
                                        return str_contains(Yii::$app->request->url, 'dashboard');
                                    },
                                ],
                                [
                                    'label' => 'Weekly',
                                    'iconClass' => 'bi bi-arrow-up-square me-1',
                                    'url' => '#',
                                    'active' => static function () {
                                        return str_contains(Yii::$app->request->url, 'dashboard');
                                    },
                                ],
                                [
                                    'label' => 'Monthly',
                                    'iconClass' => 'bi bi-capsule me-1',
                                    'url' => '#',
                                    'active' => static function () {
                                        return str_contains(Yii::$app->request->url, 'dashboard');
                                    },
                                ],
                                [
                                    'label' => 'Annually',
                                    'iconClass' => 'bi bi-capsule me-1',
                                    'url' => '#',
                                    'active' => static function () {
                                        return str_contains(Yii::$app->request->url, 'dashboard');
                                    },
                                ],
                            ],
                        ],
                        [
                            'label' => 'Orders',
                            'iconClass' => 'bi bi-bag-check',
                            'items' => [
                                [
                                    'label' => 'New',
                                    'iconClass' => 'bi bi-bag-plus me-1',
                                    'url' => '#',
                                    'active' => static function () {
                                        return str_contains(Yii::$app->request->url, 'orders');
                                    },
                                ],
                                [
                                    'label' => 'Processed',
                                    'iconClass' => 'bi bi-list-stars me-1',
                                    'url' => '#',
                                    'active' => static function () {
                                        return str_contains(Yii::$app->request->url, 'orders');
                                    },
                                ],
                                [
                                    'label' => 'Shipped',
                                    'iconClass' => 'bi bi-truck me-1',
                                    'url' => '#',
                                    'active' => static function () {
                                        return str_contains(Yii::$app->request->url, 'orders');
                                    },
                                ],
                                [
                                    'label' => 'Returned',
                                    'iconClass' => 'bi bi-arrow-return-left me-1',
                                    'url' => '#',
                                    'active' => static function () {
                                        return str_contains(Yii::$app->request->url, 'orders');
                                    },
                                ],
                            ],
                        ],
                        '<li class="border-top my-3"></li>',
                        [
                            'label' => 'Account',
                            'iconClass' => 'bi bi-person-circle',
                            'items' => [
                                [
                                    'label' => 'New...',
                                    'iconClass' => 'bi bi-person-plus me-1',
                                    'url' => '#',
                                    'active' => static function () {
                                        return str_contains(Yii::$app->request->url, 'account');
                                    },
                                ],
                                [
                                    'label' => 'Profile',
                                    'iconClass' => 'bi bi-person-vcard me-1',
                                    'url' => '#',
                                    'active' => static function () {
                                        return str_contains(Yii::$app->request->url, 'account');
                                    },
                                ],
                                [
                                    'label' => 'Settings',
                                    'iconClass' => 'bi bi-gear me-1',
                                    'url' => '#',
                                    'active' => static function () {
                                        return str_contains(Yii::$app->request->url, 'account');
                                    },
                                ],
                                [
                                    'label' => 'Sign out',
                                    'iconClass' => 'bi bi-door-closed me-1',
                                    'url' => '#',
                                    'active' => static function () {
                                        return str_contains(Yii::$app->request->url, 'account');
                                    },
                                ],
                            ],
                        ],
                    ],
                    'options' => ['class' => 'list-unstyled ps-0'],
                ]) ?>
            </div>

            <!-- Вкладка Account -->
            <div class="tab-pane fade" id="account" role="tabpanel" aria-labelledby="account-tab">

                <div class="p-2">
                    <?php if (isset($this->blocks['pageRight.Account'])): ?>
                        <?= $this->blocks['pageRight.Account'] ?>
                    <?php endif; ?>

                    <?php // TODO А можно сделать чтобы пункты сюда модуль закидывал, но глобально для всех страниц админки???? ?>
                    <?php // Стили сейчас лежат глобально, перенести в виджет ?>

                    <div class="tab-pane fade active show" id="account" role="tabpanel" aria-labelledby="account-tab">
                        <div class="p-2">
                            <div class="sidebar-buttons">
                                <button class="btn">
                                    <i class="bi bi-person-vcard"></i>
                                    <span>Profile</span>
                                </button>
                                <button class="btn">
                                    <i class="bi bi-gear"></i>
                                    <span>Settings</span>
                                </button>
                                <button class="btn">
                                    <i class="bi bi-door-closed"></i>
                                    <span>Sign out</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
