<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

/**
 * Если есть возможность, то указываем полный путь.
 * Зависимости используем когда это необходимо для логической вложенности.
 */

// Полные пути, без зависимостей
Yii::setAlias('@root', dirname(__DIR__, 2));
Yii::setAlias('@common', dirname(__DIR__, 2) . '/common');
Yii::setAlias('@frontend', dirname(__DIR__, 2) . '/frontend');
Yii::setAlias('@backend', dirname(__DIR__, 2) . '/backend');
Yii::setAlias('@console', dirname(__DIR__, 2) . '/console');
Yii::setAlias('@static', dirname(__DIR__, 2) . '/static');
Yii::setAlias('@rest', dirname(__DIR__, 2) . '/rest');
Yii::setAlias('@modules', dirname(__DIR__, 2) . '/modules');
Yii::setAlias('@themes', dirname(__DIR__, 2) . '/themes');

// Пути с зависимостями
Yii::setAlias('@var', dirname(__DIR__, 2) . '/var');
Yii::setAlias('@runtime', '@var/runtime');
Yii::setAlias('@config-dyn-gen', '@var/config');
