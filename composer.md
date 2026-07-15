## Заметка

### Стандартный репозиторий Asset Packagist:

```json
{
  "repositories": {
    "EN": {
      "type": "composer",
      "url": "https://asset-packagist.org"
    }
  }
}
```

### Локальный репозиторий в виде директории (только для разработки).

```json
{
  "repositories": {
    "local": {
      "type": "path",
      "url": "/fake-vendor/besnovatyj/packageName"
    }
  }
}
```

### Composer репозиторий gitflic.ru с авторизацией с помощью файла `auth.json`.

Так нормально и не запустился, не добавляет пакеты в автозагрузку.
`auth.json` содержит имя пользователя gitflic.ru и [транспортный токен](https://gitflic.ru/settings/transport-token).

```json
{
  "repositories": {
    "composer-gitflic": {
      "type": "composer",
      "url": "https://registry.gitflic.ru/project/besnovatyj/select2/package/-/composer"
    }
  }
}
```

```json
{
  "http-basic": {
    "registry.gitflic.ru": {
      "username": "000",
      "password": "000"
    }
  }
}
```

### Git репозиторий gitflic.ru с авторизацией с помощью файла `auth.json`.

`auth.json` содержит имя пользователя gitflic.ru и пароль пользователя gitflic.ru.

```json
{
  "repositories": {
    "select2": {
      "type": "git",
      "url": "https://gitflic.ru/project/besnovatyj/select2.git"
    }
  }
}
```

```json
{
  "http-basic": {
    "gitflic.ru": {
      "username": "000",
      "password": "000"
    }
  }
}
```

### GitHub репозиторий.

```json
{
 "repositories": [
    {
      "type": "github",
      "url": "git@github.com:username/my-package.git"
    }
  ]
}
```

---

## CKEditor 5: доставка раздельных пакетов (решение)

Редактор разнесён на пакеты (см. их readme):

- `besnovatyj/yii2-cms-ckeditor5` — ядро + базовый редактор + виджет (composer, dist коммитится).
- `besnovatyj/ckeditor5-filemanager` — файловый менеджер (composer, dist коммитится). Один пакет,
  две точки входа: `dist/index.js` (CKEditor-плагин) и `dist/standalone.js` (standalone-приложение,
  доступно как npm-export `./standalone`). FSD-ядро лежит внутри этого же пакета.
  TODO: ядро файлового менеджера вынесено в отдельный npm пакет
- `besnovatyj/ckeditor5-codemirror` — плагин CodeMirror (composer, dist коммитится).

**Принцип:** TS собирается там, где есть Node/Docker; `dist/` коммитится в git и тегается.
Потребляющий проект тянет пакеты через Composer (GitHub VCS) — **Node на проекте не нужен**.

**Важно про контекст сборки:** контейнер Node смонтирован на директорию пакета и не видит файлы
выше/в стороне. Поэтому каждый пакет собирается самодостаточно: зависимости берутся из npm-реестра
в свой `node_modules` (в контексте), межпакетных файловых путей (`file:`, alias на соседа) нет.
TODO: тоже неправда, уже обошли это ограничение

### dev (эта машина)
Уже работает через существующий path-репозиторий `./packages/besnovatyj/*` (symlink). Все три пакета
лежат там → подхватываются автоматически. Каждый собирается по кнопке build в своей папке.

### prod / другая машина (GitHub VCS + теги)
После создания GitHub-репозиториев и простановки тегов добавить в `repositories` проекта
(НЕ раньше — иначе composer будет стучаться в несуществующие репо):

```json
{
  "repositories": {
    "ckeditor5":            {"type": "vcs", "url": "https://github.com/besnovatyj/yii2-cms-ckeditor5"},
    "ckeditor5-filemanager":{"type": "vcs", "url": "https://github.com/besnovatyj/ckeditor5-filemanager"},
    "ckeditor5-codemirror": {"type": "vcs", "url": "https://github.com/besnovatyj/ckeditor5-codemirror"}
  }
}
```

Composer тянет zipball тега с уже собранным `dist/`. Опционально `.gitattributes` `export-ignore`
на `src/`, `assets/`, `*.map` — чтобы composer-архив был лёгким (dist + PHP).

### Обновление одного плагина
Пересобрать его dist в его репо → `git commit` dist → `git tag vX.Y.Z` → push →
в проекте `composer update besnovatyj/ckeditor5-filemanager`. Редактор не трогается, Node не нужен.























