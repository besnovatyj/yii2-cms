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
<!--  https://stackoverflow.com/questions/67383776/bootstrap-5-offcanvas-scrolls-back-to-top-on-close-->
<div class="sticky-md-top offcanvas-md offcanvas-start bg-body-tertiary" tabindex="-1" id="sidebarMenu"
     aria-labelledby="sidebarMenuLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="sidebarMenuLabel">Admin Panel offcanvas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu"
                aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-md-flex flex-column p-0 pt-lg-3 overflow-y-auto">
        <?php
        $backend_items = MenuAccessFilter::filter($menuItems('left-sidebar'));
        echo NavWidget::widget([
            'items' => $backend_items,
            'options' => ['class' => 'list-unstyled ps-0'],
        ]) ?>



    </div>
</div>
