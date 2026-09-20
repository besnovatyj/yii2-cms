<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace app\controllers;

use CallbackFilterIterator;
use FilesystemIterator;
use SplFileInfo;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Витрина элементов интерфейса админки (styleguide).
 *
 * Показывает демо-чанки из `views/styleguide/chunks/` в РЕАЛЬНОМ окружении админки: тот же layout,
 * та же собранная тема (`views/assets/media/`), те же ассеты. Статичной копией это не заменить —
 * витрина должна устаревать вместе с темой, а не отдельно и молча.
 *
 * Только dev: в prod любое действие отдаёт 404 ({@see beforeAction()}).
 */
final class StyleguideController extends Controller
{
    /**
     * Подписи разделов и их порядок в витрине. Чанк без подписи здесь тоже попадёт в вывод
     * (в конец, по алфавиту) — добавить новый элемент можно одним файлом, без правки контроллёра.
     */
    private const array TITLES = [
        'buttons' => 'Кнопки',
        'card' => 'Карточки',
        'alerts' => 'Уведомления',
        'forms' => 'Формы',
        'progress' => 'Прогресс',
        'pagination' => 'Пагинация',
        'table' => 'Таблицы',
    ];

    /**
     * @param \yii\base\Action $action
     * @throws NotFoundHttpException вне dev-окружения
     */
    public function beforeAction($action): bool
    {
        if (!YII_ENV_DEV) {
            throw new NotFoundHttpException('Страница не найдена.');
        }

        return parent::beforeAction($action);
    }

    /**
     * Все разделы витрины на одной странице — так видно, что элементы согласованы между собой.
     */
    public function actionIndex(): string
    {
        return $this->render('index', ['chunks' => $this->chunks()]);
    }

    /**
     * Разделы витрины: директория сканируется, чтобы список не разъезжался с файлами.
     *
     * @return array<string, string> id чанка (= имя файла без расширения) => подпись раздела
     */
    private function chunks(): array
    {
        $files = new CallbackFilterIterator(
            new FilesystemIterator($this->getViewPath() . '/chunks', FilesystemIterator::SKIP_DOTS),
            static fn(SplFileInfo $file): bool => $file->isFile() && $file->getExtension() === 'php',
        );

        $ids = [];
        foreach ($files as $file) {
            $ids[] = $file->getBasename('.php');
        }
        sort($ids);

        $known = array_values(array_intersect(array_keys(self::TITLES), $ids));
        $unknown = array_values(array_diff($ids, $known));

        $result = [];
        foreach ([...$known, ...$unknown] as $id) {
            $result[$id] = self::TITLES[$id] ?? ucfirst(str_replace(['-', '_'], ' ', $id));
        }

        return $result;
    }
}
