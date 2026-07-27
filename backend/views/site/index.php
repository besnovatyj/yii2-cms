<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/**
 * @var yii\web\View $this
 * @var string $assetUrl
 */


$this->title = 'Home';

// Панель плиток установленных модулей. Через class_exists — чтобы главная не падала, когда модуль
// дашборда не установлен (CMS модульная, набор модулей у каждой сборки свой).
$dashboardWidget = \Besnovatyj\Dashboard\widgets\dashboard\DashboardWidget::class;
if (class_exists($dashboardWidget)) {
    echo $dashboardWidget::widget();
}

echo 'Версия фреймворка: ' . Yii::getVersion();

?>
    <div class="text-danger border border-warning m-1 p-2">В проекте включён OPcache, при изменении исходников очищать кеш `opcache_reset()`. </div>
    <div class="text-danger border border-warning m-1 p-2">Проверить актуальность статьи <a href="https://habr.com/ru/companies/vk/articles/310054/" target="_blank">https://habr.com/ru/companies/vk/articles/310054/</a> и при необходимости отредактировать настройки opcache. </div>
<?php

//echo $this->render('phpinfo_to_include.php');

echo $this->render('check_components.php');
echo $this->render('HeidiSQL_password_decoder.php');
echo $this->render('../demo_chunks/buttons.php');
echo $this->render('../demo_chunks/card.php');
echo $this->render('../demo_chunks/alerts.php');
echo $this->render('../demo_chunks/forms.php');
echo $this->render('../demo_chunks/progress.php');
echo $this->render('../demo_chunks/pagination.php');
echo $this->render('../demo_chunks/table.php');
