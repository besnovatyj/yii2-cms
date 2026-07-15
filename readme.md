## Зависимости приложения

**Общие:**

```text
"besnovatyj/yii2-cms-kernel"          // Базовые классы ядра
"besnovatyj/yii2-cms-contracts"       // Контракты приложения
"besnovatyj/yii2-cms-modman"          // Модуль управления другими модулями
"besnovatyj/yii2-cms-backend-widgets" // Базовые виджеты для админки
"besnovatyj/yii2-cms-alert-widget"    // Виджет Bootstrap 5 уведомлений
"yiisoft/config"                      // Yii3 модуль конфигурации
```

**DEV зависимости:**

```text
"besnovatyj/debug-panel-modules"      // Панель отображения модулей и их конфигурации
```

## TODO:

- Профилировать потребление памяти
- Разобраться с порядком включения друг в друга всех основных конфигов приложения, раньше было логично, сейчас как-то
  запутано
