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

# Деплой

Читать прям построчно и внимательно "ansible/readme.md"

---

Системные конфиги и все файлы загружаем и правим от имени пользователя ansible, он имеет sudo без пароля.

Вся работа с командами внутри CMS от имени `www-data`, который имеет нужные доступы и не создаёт артефактов
с правами недоступными впоследствии самой CMS.

Через терминал модули устанавливаем от пользователя `www-data`:

```bash
sudo -u www-data php yii Modman/modules/install User
```

После этого требуется запустить: `sudo systemctl reload php8.4-fpm` для сброса OpCache

Секреты (`roles/web-server/app_secrets`): файлы `/etc/bescms/secrets/*` ← симлинк `/run/secrets`,
права `root:www-data 0640`, каталог `0750`. То есть читать их может только root и группа www-data.
`SecretReader::get('MYSQL_USER')` в CLI под юзером ansible:

1. файл `/run/secrets/MYSQL_USER` — `is_readable()` = false (ansible не в группе www-data);
2. `getenv('MYSQL_USER')` — пусто (env есть только в пуле FPM, `clear_env=no`);
3. default → ''.
