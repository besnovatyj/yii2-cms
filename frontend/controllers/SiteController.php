<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace frontend\controllers;

use yii\web\ErrorAction;

class SiteController extends \yii\web\Controller
{
    public function actions(): array
    {
        return [
            'error' => [
                'class' => ErrorAction::class,
                'layout' => $this->layout,
            ],
        ];
    }

    public function actionIndex(): string
    {
        return $this->render('index');
    }

    public function actionQuickTickets(): string
    {
        return $this->render('quickTickets');
    }
}
