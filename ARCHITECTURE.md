# Архитектура Yii2-CMS: паттерны, конвенции, точки расширения

Документ описывает **как устроена система в целом** и **как писать новый модуль / расширять существующий**,
а не что лежит в каждом файле. Пофайловые детали — в README пакетов
(`vendor/besnovatyj/*/readme.md`, особенно `yii2-cms-modman/ARCHITECTURE.md`).

Девиз архитектуры: **универсализация, расширяемость, консистентность-единообразие**.
Ниже он разложен на конкретные инварианты, которые нужно соблюдать в любом новом коде.

---

## 1. Карта системы: пять слоёв

| Слой | Где лежит | Что это | Правило |
|---|---|---|---|
| **Скелет приложения** | `app/{backend,frontend,console,rest}`, `app/common` | Точки входа, конфиги приложений, гейт доступа, базовые компоненты, layout'ы админки | Максимально тонкий. Ничего доменного. Не знает имён модулей |
| **SDK ядра** | `vendor/besnovatyj/yii2-cms-kernel` | `CmsModule`, `BaseMigration`, `ControllerTrait`, `UrlManagerHelperTrait`, гейт `DefaultDenyAccessControl`, `AccessHelper` | Зависит только от Yii2 и контрактов. Поведение, не метаданные |
| **Контракты** | `vendor/besnovatyj/yii2-cms-contracts` | Только интерфейсы/DTO межмодульного взаимодействия | Ни одной зависимости на модули. Один источник истины для «стыков» |
| **Модули-пакеты** | `vendor/besnovatyj/yii2-cms-*` (`extra.bescms.kind = module`) | Функциональность CMS: домен + админка + фронт | Максимальная инкапсуляция: модуль везёт свою БД-схему, UI, URL, настройки, ассеты |
| **Пакеты-библиотеки** | `vendor/besnovatyj/*` (`kind = package`) | Виджеты, behaviors, хелперы, валидаторы, инфраструктура (upload, domain-events, tree-manager, forms) | Не регистрируются как Yii-модули, не имеют admin-меню и миграций |

Плюс **темы** (`app/themes/*`) — отдельная ось представлений (см. §12) и **артефакты** (`app/var/config/*`) —
скомпилированное производное состояние (см. §5).

### Принцип «ядро не знает о модулях»

Скелет не содержит ни одного упоминания конкретного модуля: ни в `modules`, ни в `bootstrap`,
ни в URL-правилах, ни в `components`. Всё это **вклады модулей**, собираемые компилятором.
Проверка при ревью: `grep` по имени модуля в `app/{backend,frontend,common}/config` должен быть пуст.

---

## 2. Архитектурный закон: compile-not-patch

> **Состояние модулей декларативно и единично. Вся Yii-конфигурация — чистая производная от него.
> Конфиги не патчатся, а компилируются заново.**

- Единственный изменяемый источник истины — **реестр modman** (`var/config/modules-state.php`, атомарный lock-файл).
- Все конфиги, меню, карты представлений, каталоги плиток — **производные артефакты**,
  собираемые целиком (`ConfigCompiler`), а не правкой по месту.
- Запись артефакта — только `tmp + rename() + opcache_invalidate` (`AtomicWriter`).
- Откат = «вернуть прежний реестр и перекомпилировать», а не N компенсаций.
- Финальная запись реестра — последний шаг (**commit-at-end**).
- Реестр авто-восстановим: `Modman/modules/sync` пересобирает его из discovery + истории миграций.

Следствие для разработчика: **никогда не редактируйте файлы в `var/config/` руками** и не пишите
код, который их патчит. Меняется декларация в модуле → `recompile`.

---

## 3. Анатомия модуля-пакета (эталон)

```
composer.json                 # type: yii2-extension; extra.{moduleClass,moduleId,bescms,config-plugin}
src/Module.php                # extends CmsModule implements DeclaresModule, Provides*, *Provider
src/Bootstrap.php             # опционально: BootstrapInterface (подписки на события, инвалидация кэша)
src/config/
    common.php                # вклад в группу `common` (регистрация модуля, компоненты, DI, URL-правила)
    {backend,frontend,rest}.php  # пер-аппликационные вклады (через Module::appConfig())
    adminMenu.php             # пункты меню админки с `_meta.placements`
    config.php                # базовый конфиг Yii-модуля: id, params
    container.php             # DI только для самого модуля (ленивый, при init модуля)
    options.php               # настраиваемые из админки опции
    dependencies.php          # зависимости (модули, php-расширения, версии)
src/migrations/               # namespaced-миграции, extends BaseMigration
src/entities/                 # AR-сущности домена (+ entities/queries/, entities/events/)
src/repositories/             # чтение+запись ДЛЯ АДМИНКИ (видит всё, включая скрытое)
src/readModels/               # чтение ДЛЯ ФРОНТА (только публично доступное)
src/services/                 # бизнес-операции; services/manage/ — CRUD-оркестрация
src/forms/{backend,frontend}/ # формы; forms/*/search/ — фильтры GridView
src/controllers/{backend,frontend}/  # тонкие контроллёры
src/views/{backend,frontend}/ # представления; views/mail/ — письма
src/widgets/                  # виджеты модуля (+ widgets/views/, media/ с TS-исходниками)
src/urls/                     # класс-правила UrlRuleInterface (если нужны)
src/listeners/                # слушатели доменных событий
src/messages/{ru,en}/         # переводы (если модуль переводится)
```

Дополнительные каталоги допустимы (`providers/`, `settings/`, `dto/`, `helpers/`, `image/`, `contracts/`),
но **имена из таблицы выше зарезервированы за своей ролью** — не «services» вместо «repositories».

### Module.php — минимальный эталон

```php
class Module extends CmsModule implements
    DeclaresModule, ProvidesAdminMenu, ProvidesMigrations, ProvidesOptions, ProvidesDependencies
{
    public const bool   EDITABLE  = true;
    public const string VERSION   = '1.0.0';
    public const string MODULE_ID = 'MyModule';

    public static function moduleId(): string            { return self::MODULE_ID; }
    public static function moduleVersion(): string       { return self::VERSION; }
    public static function isEditable(): bool            { return self::EDITABLE; }
    public static function moduleConfig(): array         { return require __DIR__ . '/config/config.php'; }
    public static function adminMenu(): array            { return require __DIR__ . '/config/adminMenu.php'; }
    public static function options(): array              { return require __DIR__ . '/config/options.php'; }
    public static function dependencies(): array         { return require __DIR__ . '/config/dependencies.php'; }
    public static function migrationPath(): string       { return __DIR__ . '/migrations'; }
    public static function migrationNamespace(): ?string { return __NAMESPACE__ . '\\migrations'; }
}
```

Правила:
- **Метаданные — статические методы**, потому что менеджер читает их без инстанцирования модуля.
- **Тела методов — `require` файла из `config/`**, а не литерал в классе: конфиг остаётся данными.
- Значения не дублируются: `common.php` берёт их из тех же статических методов.
- `init()` переопределяется только для регистрации i18n-переводов и `controllerMap` — всё остальное
  делает `CmsModule::init()` (раскладка `controllerNamespace`/`viewPath` по приложению, DI способа A).

---

## 4. Паттерн «capability-контракты»

Модуль **не имеет** длинного абстрактного базового класса с опциональными методами.
Вместо этого он реализует ровно те интерфейсы, чьи возможности предоставляет; система проверяет
через `instanceof`, а не `method_exists()`. Это статически проверяемо и видно IDE.

### Контракты жизненного цикла (`Besnovatyj\Contracts\module\*`) — читает modman

| Контракт | Метод | Смысл |
|---|---|---|
| `DeclaresModule` | `moduleId/moduleVersion/moduleConfig/isEditable` | Обязателен всем |
| `ProvidesDependencies` | `dependencies()` | Модули, php-расширения, версии |
| `ProvidesMigrations` | `migrationPath/migrationNamespace` | БД-схема модуля |
| `ProvidesDirectories` | `directories()` | Каталоги на домене статики |
| `ProvidesAdminMenu` | `adminMenu()` | Пункты меню админки |
| `ProvidesOptions` | `options()` | Настройки, редактируемые из админки |
| `ProvidesComponents` | `components()` | Компоненты приложения (глобально) |
| `ProvidesAppConfig` | `appConfig()` | Пер-аппликационный вклад (`app-backend` и т.п.) |
| `ProvidesBootstrap` | `bootstrapClasses()` | Классы `BootstrapInterface` |
| `ProvidesLogChannels` | `logChannels()` | Каналы Monolog |

### Контракты интеграции (тоже в `yii2-cms-contracts`) — читают модули-агрегаторы

| Контракт | Кто спрашивает | Что даёт |
|---|---|---|
| `menu\MenuTargetProvider` | модуль Menu | Цели пунктов меню (`route` + карта `slug => подпись`) |
| `routing\AliasTargetProvider` | модуль RouteAlias | Цели коротких URL |
| `search\SearchableProvider` | модуль Search | Источники и документы для сквозного поиска |
| `dashboard\ProvidesDashboardWidgets` | модуль Dashboard | Плитки главной админки |
| `snippet\SnippetProvider` | модуль Snippets | Сниппеты редактора |
| `config\OptionItemsProvider` | модуль Config | Динамический список вариантов опции |
| `security\AccessAuthorizer` | ядро (гейт) | Решение «можно/нельзя» по маршруту |
| `shortcode\ShortcodeTextResolver` | любой потребитель | Разворачивание `%name%` без зависимости от модуля шорткодов |
| `theme\LayoutPathProvider`, `ViewSourcesManifest`, `ViewVariantsManifest`, `ViewVariantCatalog` | ядро/темы | Темизация |

**Ключевое свойство:** контракт — это *двусторонний шов*. Ни агрегатор не знает имён контентных
модулей, ни контентный модуль не знает об агрегаторе. Отсутствует модуль-агрегатор — метод просто
никто не вызывает; отсутствует контентный модуль — он не попадает в конфиг и не найдётся при обходе.
**Мягкая деградация вместо жёсткой зависимости** — обязательное свойство любой новой интеграции.

### Паттерн «реестр провайдеров»

Агрегатор собирает провайдеров одинаковым обходом:

```php
foreach (array_keys(Yii::$app->getModules()) as $id) {
    $module = Yii::$app->getModule((string)$id);
    if ($module instanceof SearchableProvider) { $providers[$id] = $module; }
}
```

Отключённый в modman модуль в конфиг не попадает → отдельная проверка активности не нужна.
Результат кэшируется на запрос (реестр — singleton). Альтернативный вариант того же паттерна —
**компиляция каталога в артефакт** (так сделан Dashboard: `dashboardWidgets.php`), когда обход
нужен на горячем пути.

---

## 5. Конфигурация: слои, группы, merge-plan

Сборка конфигов — рантайм-мердж движком **`yiisoft/config`** (Yii3) по плану, который генерит modman
из реестра активных модулей. Собственный composer-плагин `yiisoft/config` **отключён** —
иначе он тянул бы все установленные пакеты мимо реестра.

```
composer.json пакета
  extra.config-plugin: { common: src/config/common.php, app-backend: src/config/backend.php, ... }
        │
        ▼  (modman: MergePlanCompiler)
app/var/config/merge-plan.php        ← план: группа → пакет → файлы
        │
        ▼  (common\config\ConfigFactory → Yiisoft\Config\Config)
готовый конфиг приложения для new yii\web\Application(...)
```

**Группы = приложения** + общий слой: `common`, `app-backend`, `app-frontend`, `app-rest`, `app-console`,
плюс служебная `admin-menu`. Для всех включён `RecursiveMerge`.

Правила, которые нельзя нарушать:

1. **Define once per layer.** Один и тот же скалярный ключ нельзя задать дважды в одном слое —
   мерджер это запрещает. Отсюда, например, `language` живёт только в `common/config/main.php`,
   а `accessAuthorizer` — только в модуле user.
2. **Root — база, vendor — вклады.** Вклад vendor-слоя встаёт *перед* правилами root, поэтому
   catch-all URL ядра остаётся последним.
3. **Allowlist для `ProvidesAppConfig`.** Модулю разрешены `components`, `params` и
   `as access.allowActions`. `as access.class`, его `rules`/`denyCallback` вырезаются: **гейт
   принадлежит ядру**, модуль может лишь дополнить whitelist. Расширение полномочий модулей =
   расширение этого allowlist в одном месте.
4. **Замыкания остаются замыканиями** — файлы конфигов `require`-ются, ничего не сериализуется.
5. `*-local.php` (окружения `app/environments/{dev,prod}`) входят в план как опциональные;
   env-слой самого `yiisoft/config` не используется.

### Куда что класть

| Что | Куда |
|---|---|
| Регистрация модуля | `config/common.php` → `modules[Module::moduleId()]` |
| Компонент, нужный везде | `config/common.php` → `components` (или `ProvidesComponents`) |
| Компонент, зависящий от приложения (identity, авторизатор) | `config/{app}.php` через `Module::appConfig()` |
| DI, нужный только внутри модуля | `config/container.php` (ленивый, при `init()` модуля) |
| DI, нужный снаружи модуля (виджет темы, консоль, другой модуль) | `config/common.php` → `container.singletons` |
| URL-правила фронта | `config/common.php` → `components.frontendUrlManager.rules` |
| Меню админки | `config/adminMenu.php` (группа `admin-menu`) |

---

## 6. Три способа проводки DI

| Способ | Файл | Когда выполняется | Для чего |
|---|---|---|---|
| **A** | `src/config/container.php` | Лениво, при `init()` Yii-модуля | Зависимости, нужные только внутри модуля |
| **B** | `composer.json` → `extra.bootstrap` | Глобально, бутстрапом Yii2 (L1) | Инфраструктурные пакеты (`upload`, `smart-domain-events`) |
| **C** | `ProvidesBootstrap` + `bootstrap` в `common.php` | Глобально, но **гейтится modman** (L2) | Модули: подписки на события, инвалидация кэша |

**Правило:** для модулей — способ C (выключил модуль → вклад исчез). Способ B — только для пакетов
без модульного жизненного цикла. Важно: пока пакет не перевыпущен (push + tag + `composer update`),
L1 из `extensions.php` может дублировать L2 → дубли слушателей.

Общий принцип DI: **зависимости внедряются конструктором**, строки/алиасы резолвятся в
composition root (`container.php` / `common.php`). Сервис не должен звать `Yii::getAlias()`
или `Yii::$app->...` — путь и компонент ему передают. Контроллёры получают сервисы через
конструктор (`__construct($id, $module, MyService $service, $config = [])`).

---

## 7. Внутренние слои модуля и границы между ними

```
Controller (тонкий)
   ├─ backend  → Form (BaseForm) → Service (services/manage/*) → Repository → Entity (AR)
   └─ frontend → ReadModel (readModels/*) → Entity (AR, query->visible())
```

| Слой | Отвечает | Чего НЕ делает |
|---|---|---|
| **Controller** | Разбор запроса, вызов сервиса/read-модели, рендер, flash/redirect | Не содержит бизнес-логики, не ходит в AR напрямую (кроме `findModel`) |
| **Form** | Валидация ввода, приведение типов, композиция вложенных форм | Не пишет в БД |
| **Service** | Бизнес-операция: транзакция, оркестрация нескольких репозиториев, побочные эффекты | Не знает про `session flash`, HTTP, GridView |
| **Repository** | CRUD и выборки **для админки** — видит скрытое и черновики | Не фильтрует по публичности |
| **ReadModel** | Выборки **для фронта** — только публично доступное (`->visible()`) | Не пишет |
| **Entity (AR)** | Инварианты домена: `create()`, `edit()`, `activate()`, `isActive()` | Не оркестрирует другие агрегаты |

**Дублирование методов между `repositories` и `readModels` — намеренное.** Один и тот же метод на
два контекста рано или поздно утекает скрытым контентом на публичную страницу. Ничего из `readModels`
не «оптимизируем» в общий метод.

Дополнительно:
- Именованные scope'ы — в `entities/queries/*Query.php` (`visible()`, `active()`), а не в репозитории.
- Транзакции — `Yii::$app->db->beginTransaction()` в сервисе (или `transactions()` в AR для простых случаев).
- Формы наследуют `Besnovatyj\Forms\BaseForm` (приводит POST-строки к typed properties PHP 8)
  или `CompositeForm` (вложенные формы, напр. `$form->meta`, `$form->tags`).
- Пагинатор в админке — `Besnovatyj\BackendWidgets\pagination\LinkPager`, колонки — `grid\ActionColumn`.

---

## 8. Доменные события

Пакет `yii2-cms-smart-domain-events`: сущность-агрегат записывает события, репозиторий их
диспатчит после успешного сохранения.

```php
$contact = Contact::create(...);       // внутри: $this->recordEvent(new ContactMessageSent($contact));
$this->repo->save($contact);           // внутри: $dispatcher->dispatchAll($contact->releaseEvents());
```

Цепочка диспетчеров — декораторы: `DeferredEventDispatcher` (копит до конца транзакции) →
`AsyncEventDispatcher` (кладёт в `yii2-queue`/Redis) → воркер → `SimpleEventDispatcher` → слушатели.

Конвенции: события — `src/entities/events/*`, слушатели — `src/listeners/*`, карта
«событие → слушатели» объявляется в конфиге модуля. Используйте события для **межмодульных**
и **побочных** эффектов (письма, уведомления, индексация), а не для основной логики операции.

---

## 9. Безопасность и доступ

Два разных элемента, которые нельзя смешивать:

- **Гейт** — `DefaultDenyAccessControl` (ядро, `as access` в конфиге закрытого приложения).
  Есть всегда, модуль не может его снять. Backend закрыт по умолчанию, frontend открыт.
- **Авторизатор** — компонент `accessAuthorizer` (контракт `AccessAuthorizer`). Даёт решение
  «можно/нельзя». По умолчанию — `DenyAllAuthorizer`; модуль `user` перекрывает `RbacAuthorizer`.

**Fail-closed:** нет авторизатора, он не резолвится или не реализует контракт → доступ **запрещён**.
Поломка биндинга приводит к запрету, а не к 500 с открытым обходом.

Модуль расширяет доступ **только** через `ProvidesAppConfig` → `as access.allowActions`
(вход, callback капчи и т.п.). В представлениях кнопки/пункты фильтруются через
`Besnovatyj\Kernel\security\AccessHelper::checkRoute()` / `filterActionColumn()` — так вьюха не
зависит от модуля `user`.

Прочие ядровые правила: `goReferer()` из `ControllerTrait` (защита от open redirect — сверка хоста
с `UrlManager::$hostInfo`, редирект относительным путём), `handleDomainException()` (в debug —
текст, в проде — общая фраза), `VerbFilter` на мутирующие экшены.

---

## 10. Данные и миграции

- Миграции **только namespaced**, в `src/migrations/`, наследуют `BaseMigration`.
- Применяет их **modman** (`ModuleMigrationRunner`) при установке/обновлении модуля,
  с записью владения (`{{%modman_migration}}`) и синхронизацией со штатной `{{%migration}}`.
- Конвенция проекта: **правим существующие create-миграции**, а не добавляем alter-миграции
  (модуль ставится начисто; alter-цепочки ломают идею «схема = снимок»).
- Внешние ключи — отдельной завершающей миграцией (`*_create_*_foreign_key_constraints.php`).
- Таблицы именуются с префиксом модуля: `{{%blog_posts}}`, `{{%snippet_groups}}`.
- Установка на проде: `sudo -u www-data php yii Modman/modules/install <Id>` + `systemctl reload php8.4-fpm`
  (OPcache с `validate_timestamps=0`).

---

## 11. URL и маршрутизация

- Два именованных `UrlManager`-компонента: `frontendUrlManager` и `backendUrlManager`;
  `urlManager` каждого приложения — замыкание, возвращающее нужный. Это даёт **межприложенческую
  генерацию ссылок**: админка строит фронтовые URL через `UrlManagerHelperTrait`
  (`getFrontendRoute()`, `getAbsoluteFrontendRoute()`).
- **URL-правила модуля — вклад в `frontendUrlManager` группы `common`** (не `app-frontend`:
  компонент есть в обоих приложениях). Гейтятся modman: выключил модуль — правила исчезли.
- Первый сегмент роута — **реальный id модуля с заглавной** (`Blog/post/view`), публичная часть URL
  остаётся строчной.
- Раскладка контроллёров задаётся `CmsModule::init()`: фронт — `controllers\frontend` (сегмент
  `frontend` спрятан в namespace, `viewPath` смещён в `views/frontend`), консоль — `commands`,
  бэкенд — `controllers` (сегмент `backend` остаётся в маршруте).
- Сложная адресация (деревья, вложенные слаги, 301-нормализация) — **класс-правило** `UrlRuleInterface`
  в `src/urls/`. Из-за DI-конструируемых класс-правил кэш правил `UrlManager` намеренно выключен.
- Короткие «красивые» URL из админки — модуль `RouteAlias` через `AliasTargetProvider`;
  выключение модуля даёт прозрачный fallback на обычные URL.
- Адресация целей меню/алиасов унифицирована на **`slug`**.

---

## 12. Темизация и представления

Две независимые оси, сведённые в `pathMap` компонента `Theme`:

1. **Ось модулей** — артефакт `moduleViewSources.php` (генерит modman): `moduleId => алиас каталога views`.
2. **Ось темы** — активная тема, накладываемая оверлеем по единой конвенции
   `@themes/{theme}/modules/{ModuleId}/views/...`.

Результат кэшируется в `themePathMap.{theme}.php` (per-theme, с mtime-инвалидацией против манифеста).
Переключение темы = чтение другого файла.

- Layout модуля берётся из активной темы (`CmsModule::getLayoutPath()` через контракт `LayoutPathProvider`),
  лениво — на момент рендера, а не `init()`.
- **Письма темизируются той же конвенцией**: `mailer.view.theme` — тот же `Theme`,
  письмо модуля `src/views/mail/...` перекрывается темой.
- **Варианты представления** (выбираемые в админке шаблоны): рядом с `view.php` кладётся
  `view.variants/{landing.php, raw.php, _labels.php}` → сканер темы пишет `viewVariants.{theme}.php`,
  потребитель читает через `ViewVariantCatalog` (DI-биндинг из `common.php` пакета тем).
  Нет пакета тем — потребитель мягко откатывается к базовому представлению.
- Ридеры артефактов делают **self-heal**: файла нет — сгенерировать один раз, а не сканировать каждый запрос.
- Ассеты темы: `@themes/{name}/assets/web`, публикуются один раз за запрос (`$this->theme->getUrl(...)`).

---

## 13. Настройки, редактируемые из админки

Модуль объявляет `src/config/options.php`:

```php
'my_param' => [
    'path' => 'modules.mymodule.params.my_param',   // куда применить значение
    'label' => 'Параметр', 'description' => '...',
    'category' => 'MyModule',                        // раздел UI; по умолчанию — id модуля
    'rules' => [['required'], ['string']],
    'inputOptions' => ['type' => 'dropdown', 'itemsProvider' => MyItems::class],
],
```

- Модуль `Config` собирает опции всех активных модулей (`ConfigCollector`), хранит значения
  (`PhpFileStorage`/`DatabaseStorage`) и применяет их к объекту модуля при бутстрапе (`ConfigApplier`).
- Если список вариантов — это **состав системы** (установленные ядра поиска, движки редактора, темы),
  используется `OptionItemsProvider`: объявление остаётся статичным (важно для контрольной суммы
  манифеста), список собирается в рантайме и одновременно служит правилом валидации.
- Читать настройку в коде: `Yii::$app->getModule('MyModule')->params['my_param']`, а лучше — через
  собственный typed-объект настроек, собираемый фабрикой в `container.singletons`
  (образец — `SearchSettings` / `SearchSettingsFactory`).

---

## 14. Каталог сквозных точек расширения

| Точка | Контракт / механизм | Как подключиться |
|---|---|---|
| Меню админки | группа `admin-menu` + `MenuCompiler` | `adminMenu.php` с `_meta.placements` (location, group, priority) |
| Меню фронта | `MenuTargetProvider` | `menuTargets()` + `menuCandidates($route)` |
| Короткие URL | `AliasTargetProvider` | `aliasTargets()` + `aliasSlugs($route)` |
| Сквозной поиск | `SearchableProvider` | `searchSources()` + `searchDocuments($type)` (генератор, `each(100)`, сырые поля) |
| Движок поиска | `SearchEngineProvider` (в пакете Search) | `searchEngines()` → `SearchEngineDescriptor` |
| Плитки дашборда | `ProvidesDashboardWidgets` | `dashboardWidgets()` → `DashboardWidgetDescriptor` + виджет тела карточки |
| Очистка кэшей/мусора | `params.endpoints.clear` в `config.php` + `ClearEndpointInterface` | пара эндпоинтов `getData`/`clear` |
| Сниппеты редактора | `SnippetProvider` | дерево групп/сниппетов; агрегатор — компонент `snippetCatalog` |
| Шорткоды | компонент `shortcode` + `ShortcodeTextResolver` | `registerText()` / `registerWidget()`, обычно в `Module::init()` |
| Редактор WYSIWYG | фасад `Besnovatyj\Editor\EditorWidget` + адаптеры движков | во вьюхе всегда фасад, движок выбирается настройкой |
| Деревья | `yii2-cms-tree-manager` | `TreeManager` в DI + `TreeController` в `controllerMap` + `TreeDataSource` |
| Загрузка файлов | `UploadBehavior` (`yii2-cms-upload`), Flysystem-монтирования (`yii2-cms-file`) | behavior на AR + профили превью |
| Логирование | `ProvidesLogChannels` | спека канала Monolog, мёржится по id канала |
| Фазы жизненного цикла модулей | `ModuleLifecycleDispatcher` | подписка в своём `Bootstrap.php` |

**Правило добавления новой точки расширения:** интерфейс + DTO кладутся в `yii2-cms-contracts`,
агрегатор собирает реализации обходом модулей с `instanceof`, потребление — опционально и
деградирует мягко. Никаких `method_exists()` и никаких прямых зависимостей агрегатор ↔ поставщик.

---

## 15. Фронтенд и ассеты

- Виджеты с клиентской частью — **отдельные пакеты по ответственности** (select2, datetime, videojs,
  altcha, switcher-column, tree-widget-core), а не внутри модулей-потребителей.
- TypeScript strict, сборка **esbuild**; исходники — `media/` или `assets/src/`, бандл — `dist/`
  и **коммитится/едет с пакетом** (composer-потребителю npm не нужен).
- Интерфейсы для всех API-ответов; DOM-виджеты framework-agnostic (порт-интерфейс вроде
  `ITreeDataSource`), Yii-специфика — в тонком адаптере.
- Bootstrap 5 кастомизируется через SCSS-переменные; классы Bootstrap напрямую не переопределяются.
- В админке допустим HTMX (`hx-*`), ассеты подключаются вручную.
- Ошибки AJAX — нативный `ErrorHandler`: контроллёр бросает `HttpException`, клиент разбирает тело
  при `!response.ok` (единый формат вместо самодельных `ajaxError`).

---

## 16. Кэш, артефакты, инвалидация

- Кэш приложения — **APCu**, общий для backend/frontend процессов: правка из админки видна фронту.
- Инвалидация — `TagDependency` с крупным помолом; подписки на AR-события живут в `Bootstrap.php`
  модуля (образцы: `blog_taxonomies`, `route_aliases`).
- Артефакты (`var/config/*`) пишутся атомарно с `opcache_invalidate()`; на проде после CLI-операций
  нужен `reload php-fpm`, из админки — самоинвалидация.
- Сессии и очередь — Redis; очередь используется для асинхронных доменных событий.

---

## 17. Чек-лист: создаю новый модуль

1. `composer.json`: `type: yii2-extension`, `extra.moduleId`, `extra.moduleClass`,
   `extra.bescms.kind = module`, `extra.config-plugin` (`common`, при необходимости `admin-menu`,
   `app-backend`, …), PSR-4 `Besnovatyj\<Name>\` → `src/`.
2. **Прямые зависимости объявить явно** (`kernel`, `contracts`, `forms`, `helpers`, …) — транзитивных
   не оставлять, пакеты разъедутся по репозиториям.
3. `src/Module.php`: `extends CmsModule` + нужные capability-контракты; метаданные — статические,
   тела — `require config/*.php`.
4. `src/config/common.php`: регистрация модуля через `Module::moduleConfig()/moduleVersion()`,
   при необходимости `components`, `container.singletons`, URL-правила.
5. Слои: `entities` (+`queries`) → `repositories` / `readModels` → `services` → `forms` → `controllers` → `views`.
6. Миграции namespaced на `BaseMigration`; таблицы с префиксом модуля.
7. `options.php` для всего, что админ должен менять без деплоя; `directories()` — если нужна статика.
8. Интеграции — только через контракты (`MenuTargetProvider`, `SearchableProvider`, …).
9. Никакой проектной специфики: доменов, путей, id конкретного сайта в коде и дефолтах быть не должно —
   только нейтральные плейсхолдеры.
10. Установка: `php yii Modman/modules/install <Id>` (на проде — от `www-data`), затем reload FPM.

## 18. Чек-лист: расширяю существующий модуль

- Новая сущность → полный набор слоёв (query-scope `visible()`, репозиторий, read-модель, форма, сервис).
- Новая настройка → `options.php`, а не константа в коде.
- Новая интеграция с чужим модулем → контракт в `yii2-cms-contracts`, не прямая зависимость.
- Новая страница фронта → правило в `common.php` модуля, не в `frontend/config/url-manager.php`.
- Новый шаблон, который админ должен выбирать → `{view}.variants/` в теме.
- Новый ассет → в пакет-виджет, если переиспользуем; иначе в `widgets/*/media` с esbuild.
- После правок конфигов/манифестов — `recompile`; после правок vendor-пакета — push + tag + `composer update`.

---

## 19. Инварианты и антипаттерны

**Инварианты (нарушение = архитектурная ошибка):**

1. Ядро не знает имён модулей. Модуль не знает имён других модулей — только контракты.
2. Артефакты не редактируются руками и не патчатся кодом — только перекомпилируются.
3. Гейт доступа принадлежит ядру; модуль может лишь дополнить whitelist.
4. Fail-closed: отсутствие/поломка авторизатора = запрет.
5. `readModels` отдают только публично доступное. Всегда.
6. Метаданные модуля читаются статически, без инстанцирования Yii-модуля.
7. Отсутствие опционального модуля-агрегатора не ломает поставщика (мягкая деградация).
8. Зависимости — через конструктор; `Yii::$app` — только в composition root и адаптерах.
9. Прямые зависимости пакета объявлены явно в его `composer.json`.
10. Комментарии пользователя в коде не удаляются.

**Антипаттерны:**

- `method_exists()`/утиная типизация вместо контракта.
- Хардкод модуля в конфиге приложения или в другом модуле.
- Бизнес-логика в контроллёре или в `beforeSave()` AR.
- Общий метод выборки «и для админки, и для фронта».
- Ручная сборка зависимостей (`new Service(new Repo())`) вместо DI.
- Проектная специфика (домены, id, пути конкретного сайта) в vendor-пакете.
- Рантайм-оверхед (лишний file I/O, сканирование ФС) ради dev-удобства.

---

## 20. Направления развития: что унифицировать и переделать

Ниже — наблюдения по текущему состоянию кода, сгруппированные по девизу. Это материал для
предстоящего общего анализа архитектуры, а не список задач к немедленному исполнению.

### 20.1 Консистентность (наибольший разрыв между заявленным и фактическим)

| Наблюдение | Факт | Что даёт унификация |
|---|---|---|
| **Формы мимо `BaseForm`** | ~74 файла наследуют `BaseForm`/`CompositeForm`, ~100 — напрямую `yii\base\Model` (в основном search-формы и старые модули) | Единое приведение типов PHP 8, отсутствие «случайных» `TypeError` в проде |
| **Нет базового класса search-форм** | Каждый `*Search` сам создаёт `ActiveDataProvider`, свою пагинацию (`pageSize` 15/20/100 вразнобой), свой `load+validate` | `BaseSearchForm` с контрактом `search(array $params): DataProviderInterface`, едиными дефолтами пагинации/сортировки и `where('0=1')` при невалидном фильтре |
| **`declare(strict_types=1)`** | ~905 из ~2009 PHP-файлов пакетов (≈45%) | Требование AGENTS.md выполняется выборочно; поэтапное включение по пакетам |
| **PHPDoc** | ~600 файлов без единого docblock | То же требование AGENTS.md; в первую очередь — публичные API пакетов |
| **i18n** | Каталоги `messages/` есть у 4 модулей из ~40, `Yii::t()` в 31 файле; остальное — русские строки в коде | Либо явно зафиксировать «UI по-русски, переводов нет» как политику, либо ввести конвенцию «категория перевода = moduleId» и применить массово. Половинчатость хуже любого из решений |
| **`AccessHelper` в UI** | Используется в 28 файлах при 95 backend-контроллёрах | Кнопки действий фильтруются по правам непоследовательно: пользователь видит то, что ему запретят |
| **DI в `Module.php`** | `menuCandidates()`/`searchDocuments()` создают репозитории через `new` (blog, page) | Единый способ: `Yii::createObject()`/DI, иначе провайдеры не тестируемы и не подменяемы |
| **Разнобой слоистости** | `blog-new` использует `models/dto/exceptions` вместо `entities/repositories/readModels`; `shop`/`run-shop`/`catalog` — три поколения одной идеи | Явно решить судьбу дублей (канон / архив / удаление) и привести выживших к общему скелету |

### 20.2 Универсализация (повторяющийся код, который просится в ядро)

1. **Реестр провайдеров.** Обход `Yii::$app->getModules()` + `instanceof` написан минимум четырежды
   (search, menu, snippets, clear-manager), у dashboard — свой путь через compile-артефакт.
   Просится один `ModuleCapabilityRegistry<T>` в kernel: обход + кэш на запрос + опционально
   компиляция в артефакт. Тогда добавление новой точки расширения = один интерфейс, ноль инфраструктуры.
2. **Инвалидация кэша по AR-событиям.** `Bootstrap.php` блога и route-alias — один и тот же код с
   другим тегом. Просится декларация: `['tag' => 'blog_taxonomies', 'classes' => [Taxonomy::class]]`
   и общий bootstrap-класс.
3. **Артефакты.** Пишут трое (modman `AtomicWriter`, темы `ArrayExportHelper`, config-модуль
   `PhpFileStorage`), инвалидация OPcache и self-heal реализованы независимо. Просится единый
   `ArtifactStore` (путь + атомарная запись + opcache + mtime-инвалидация + self-heal) и одна команда
   `flush` вместо `Themes/cache/flush` + модульных clear-эндпоинтов.
4. **Пункты `adminMenu`.** В каждом пункте вручную пишется замыкание `active` со `str_contains`/`preg_match`
   по URL. Просится хелпер `MenuActive::byRoutePrefix('/Blog/backend/post')` — короче и не расходится
   с реальным роутом.
5. **Профили превью в сущности.** `Post::behaviors()` содержит захардкоженные размеры (и собственный
   TODO автора). Просится объявление профилей на уровне модуля/настроек, а сущность лишь ссылается на имя набора.
6. **Слой REST.** `app/rest` практически пуст, при этом oauth2 и `app-rest`-группа уже есть.
   Если API появится — вводить его сразу как контракт (`ProvidesRestResources`), а не как каталог
   контроллёров в скелете, иначе инвариант «ядро не знает модулей» будет нарушен именно там.

### 20.3 Расширяемость (двойные каналы — источник будущих расхождений)

1. **Два канала для компонентов**: контракт `ProvidesComponents` (используют 2 модуля) и
   `components` в `config/common.php` (используют все остальные). Функционально это одно и то же.
   Оставить один канал; второй — либо удалить, либо явно задокументировать как «компоненты,
   которые modman обязан видеть при планировании» с проверкой конфликтов имён.
2. **Два уровня bootstrap**: L1 (`composer.extra.bootstrap`, вне гейта modman) и L2
   (`ProvidesBootstrap` + `bootstrap` в `common.php`, гейтится). Пока пакет не перевыпущен, вклады
   дублируются → дубли слушателей. Нужна проверка «L1 и L2 одновременно» на этапе планирования
   (частично есть: `L1BootstrapCheck`) и явная политика для пакетов-не-модулей.
3. **Где живут контракты плагинов.** Часть — в `yii2-cms-contracts` (`SearchableProvider`,
   `SnippetProvider`), часть — в пакете-хосте (`SearchEngineProvider` в модуле Search,
   `ClearEndpointInterface` в clear-manager, `ImageOwnerInterface` в images). Правило нужно одно:
   либо «все межмодульные контракты — в contracts», либо «контракт плагина принадлежит хосту, а
   contracts — только то, что знает ядро». Сейчас разработчик каждый раз гадает.
4. **Версия модуля.** `Module::VERSION` дублирует версию из `installed.json` (источник истины —
   composer). Константу имеет смысл оставить только как fallback (уже сделано) и не обновлять руками.
5. **Тестов нет вообще** (`phpunit` не сконфигурирован ни в одном пакете). При такой контрактной
   архитектуре наибольшую отдачу дают три вида тестов, и они дешёвые:
   контракт-тесты (каждый `Provides*` возвращает валидную структуру),
   тест детерминизма компиляции (install/uninstall → побайтово те же артефакты),
   тест `BaseForm` (приведение типов). Это защищает ровно те инварианты, на которых стоит система.
6. **Статический анализ.** `psalm.xml`, `phpcs.xml`, `phpdoc.xml` в корне есть — но при 45% strict_types
   и 600 файлах без PHPDoc они, вероятно, не в CI. Включение хотя бы на уровне «не хуже, чем было»
   (baseline) закрепит консистентность вместо ручных ревизий.

### 20.4 Что уже сделано хорошо (не трогать без веской причины)

- Compile-not-patch с единым реестром и авто-восстановлением (`sync`) — снят целый класс болей.
- Capability-контракты вместо `method_exists()` — статически проверяемая расширяемость.
- Fail-closed гейт, отделённый от авторизатора.
- Разделение `repositories` / `readModels` по контексту доступа.
- Двухосевая темизация с per-theme кэшем и self-heal ридерами.
- Опциональность всего: любой агрегатор можно выключить, и система деградирует мягко.

---

## 21. Куда смотреть дальше

| Тема | Источник |
|---|---|
| Менеджер модулей: слои, реестр, lifecycle | `vendor/besnovatyj/yii2-cms-modman/ARCHITECTURE.md` |
| Миграция на `yiisoft/config` | `/workspace/TODO/TODO_YII3_CONFIG.MD` |
| Настройки модулей | `vendor/besnovatyj/yii2-cms-config/readme.md` |
| Сквозной поиск (фасад + ядра) | `vendor/besnovatyj/yii2-cms-search/README.md` |
| Доменные события | `vendor/besnovatyj/yii2-cms-smart-domain-events/readme.md` |
| Деревья | `vendor/besnovatyj/yii2-cms-tree-manager/README.md` (+ `src/nested-sets-manager/README.md`) |
| Загрузка файлов / хранилища | `vendor/besnovatyj/yii2-cms-upload/readme.md`, `yii2-cms-file/` |
| Дашборд, сниппеты, алиасы, шорткоды | одноимённые `readme.md` в пакетах |
| Деплой | `ansible/readme.md`, `app/readme.md` |
