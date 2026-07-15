<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace rest\controllers;

use yii\rest\Controller;

class SiteController extends Controller
{
    public function actionIndex(): array
    {
        return [
            'version' => '1.0.0',
        ];
    }
    public function actionPing(): string
    {
        return 'pong';
    }
}
