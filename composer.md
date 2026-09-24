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
























