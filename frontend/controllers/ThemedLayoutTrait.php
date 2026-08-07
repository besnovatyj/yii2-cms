<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace frontend\controllers;

use Besnovatyj\Contracts\theme\LayoutPathProvider;
use Yii;

/**
 * Отдаёт layout из АКТИВНОЙ ТЕМЫ обычным (не-{@see \Besnovatyj\Kernel\module\CmsModule})
 * контроллёрам приложения app-frontend.
 *
 * Зачем: тема хранит макеты в отдельном каталоге `@themes/{theme}/layouts/`, который НЕ покрывается
 * `pathMap` темизации (тот ремапит `@app/views/...` → `@themes/{theme}/views/...`). Каталог макетов
 * темы отдаёт узкий контракт {@see LayoutPathProvider::getLayoutsPath()} — так же, как его берёт
 * `CmsModule` для контроллёров модулей. Обычные app-контроллёры (`SiteController` и пр.) этого
 * механизма лишены и по умолчанию рендерятся в голом скелете `@app/views/layouts/main.php`.
 *
 * Трейт переопределяет {@see \yii\base\Controller::findLayoutFile()} — единую точку резолва макета
 * в Yii, поэтому тема применяется ко ВСЕМ действиям контроллёра, включая {@see \yii\web\ErrorAction}.
 * Имя макета берётся из `$this->layout` (по умолчанию `main`); `layout === false` (макет отключён)
 * и отсутствие пакета тем — прозрачно делегируются родителю (мягкая деградация).
 */
trait ThemedLayoutTrait
{
    /**
     * @param \yii\base\View $view
     * @return string|bool абсолютный путь к макету темы, либо результат родительского резолва.
     */
    public function findLayoutFile($view)
    {
        $theme = Yii::$app->view->theme ?? null;
        if ($this->layout !== false && $theme instanceof LayoutPathProvider) {
            $name = is_string($this->layout) && $this->layout !== '' ? $this->layout : 'main';
            $file = $theme->getLayoutsPath() . '/' . $name . '.php';
            if (is_file($file)) {
                return $file;
            }
        }

        return parent::findLayoutFile($view);
    }
}
