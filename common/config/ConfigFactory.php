<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace common\config;

use Yii;
use Yiisoft\Config\Config;
use Yiisoft\Config\ConfigInterface;
use Yiisoft\Config\ConfigPaths;
use Yiisoft\Config\Modifier\RecursiveMerge;

/**
 * Обёртка ядра над движком сборки конфигов {@see \Yiisoft\Config\Config} (Yii3).
 *
 * Собирает конфигурацию приложения РАНТАЙМ-мерджем по плану `var/config/merge-plan.php`, который
 * генерит modman из реестра активных модулей (плагин самого yiisoft/config отключён —
 * `allow-plugins: {"yiisoft/config": false}`, иначе он тянул бы все установленные пакеты мимо реестра).
 *
 * Замыкания (`urlManager`-фабрики, `active` меню, компонент-фабрики) остаются в PHP-файлах модулей и
 * `require`-ятся как есть — никакой сериализации. Публичный шов — класс `Config` и файл плана; внутренние
 * `MergePlan`/`Merger` напрямую не трогаем.
 *
 * Пути: root-файлы адресуются относительно `@root` (configDirectory=''), vendor-файлы — `vendor/{pkg}/{file}`.
 * Окружение yiisoft/config не используется (env-слой '/'): различие dev/prod обеспечивает существующий
 * `app/init` (подмена `*-local.php` из `app/environments/`), а `*-local.php` входят в план как опциональные.
 *
 * @see /TODO_YII3_CONFIG.MD — дизайн и порядок внедрения.
 */
final class ConfigFactory
{
    /** Группы = приложения (+ общий слой и params); совпадают с ключами merge-plan. */
    public const string GROUP_COMMON = 'common';
    public const string GROUP_PARAMS = 'params';

    /** Относительный (от @root) путь плана, который пишет modman. */
    private const string MERGE_PLAN_FILE = 'var/config/merge-plan.php';

    /** Группы, для которых массивы сливаются рекурсивно (components, as access, params …). */
    private const array RECURSIVE_GROUPS = [
        self::GROUP_COMMON,
        self::GROUP_PARAMS,
        'app-backend',
        'app-frontend',
        'app-rest',
        'app-console',
    ];

    /**
     * Один экземпляр движка на весь запрос: {@see Config} кэширует собранные группы у себя, поэтому
     * повторные обращения (напр. группа `admin-menu` из двух сайдбаров) не пересобираются и merge-plan
     * не перечитывается. Статик — потому что ConfigFactory инстанцируют в разных местах (entry-point,
     * сайдбары), а движок должен быть общим.
     */
    private static ?ConfigInterface $config = null;

    private function config(): ConfigInterface
    {
        return self::$config ??= $this->makeEngine();
    }

    /**
     * Создаёт НОВЫЙ движок сборки (без общего статического кэша).
     *
     * Боевой процесс собирает ровно ОДНУ группу в свежем движке за запрос (см. точки входа `pub/index.php`).
     * Инструментам (вьювер конфига), которым нужно собрать НЕСКОЛЬКО групп в одном запросе, следует брать
     * изолированный движок ({@see getIsolated()}), а не прогретый рантаймом статический `config()` — иначе
     * поведение отличается от боевого (движок держит собранные группы в себе на весь запрос).
     */
    private function makeEngine(): ConfigInterface
    {
        return new Config(
            new ConfigPaths(Yii::getAlias('@root'), '', 'vendor'),
            null, // окружение '/' по умолчанию — см. описание класса
            [RecursiveMerge::groups(...self::RECURSIVE_GROUPS)],
            // paramsGroup = null: НЕ строим группу params. Инъекцию `$params` в конфиг-файлы мы не
            // используем (app-*/main.php собирают свой $params через array_merge), поэтому отдельная
            // группа params была лишним дублем в памяти. Экономия памяти/CPU без потери функциональности.
            null,
            self::MERGE_PLAN_FILE,
        );
    }

    /**
     * Собранный конфиг приложения для `new yii\web\Application(...)` / консоли.
     *
     * @param string $app Идентификатор приложения = имя группы (`app-backend`, `app-frontend`, …).
     * @return array Готовый Yii2-конфиг.
     */
    public function app(string $app): array
    {
        return $this->config()->get($app);
    }

    /**
     * Собранная группа по имени (напр. `admin-menu` — вклады меню всех модулей). Кэшируется движком.
     */
    public function get(string $group): array
    {
        return $this->config()->get($group);
    }

    /** Есть ли такая группа в плане (для диагностики/гварда до генерации плана). */
    public function has(string $group): bool
    {
        return $this->config()->has($group);
    }

    /**
     * Собрать группу в ИЗОЛИРОВАННОМ свежем движке — как боевой процесс, а не через прогретый статический.
     *
     * Для диагностики (вьювер конфига modman), где в одном web-запросе собирают несколько групп: общий
     * статический движок уже прогрет группой текущего приложения (app-backend), и добор других групп через
     * него ведёт себя не как отдельный процесс. Свежий движок повторяет боевое «одна группа за процесс».
     */
    public function getIsolated(string $group): array
    {
        return $this->makeEngine()->get($group);
    }
}
