<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

?>
<div class="container">
    <div class="row">
        <h2>Не из темы</h2>
        <div class="col-12">
            <div class="p-3 bg-warning blue-100">
                <?php if (isset($this->theme)): ?>
                    <div><b>Инфо о теме:</b></div>
                    <div>
                        Название: <?= $this->theme->name ?>
                    </div>
                    <div>
                        Базовая директория: <?= $this->theme->basePath ?>
                    </div>
                <?php else: ?>
                    <div>Модуль темы выключен, темизация приложения отсутствует</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
