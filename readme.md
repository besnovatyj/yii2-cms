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

composer install --no-dev --optimize-autoloader

php init с выбором prod (генерит cookieValidationKey, раскладывает -local.php из environments/prod);

-----------------------------------

Порядок после переустановки сервера прежний: 
`make full-deploy` (stage-4 теперь ставит и composer), 
затем `make release`, 
потом `stage-5a` (certbot), 
`stage-5b` (queue) 
и `stage-6`. 
Скелет web-корней, который nginx-роль создаст до релиза, release-роль сама уберёт перед первым клоном.

-------------------------



"/workspace/TO_SERVER/backend/pub" - публичный корень админки
"/workspace/TO_SERVER/backend/pub/assets" - директория ресурсов для админки
"/workspace/TO_SERVER/frontend/pub" - публичный корень сайта
"/workspace/TO_SERVER/frontend/pub/assets" - директория ресурсов для сайта
"/workspace/TO_SERVER/static" - корень домена статики
"/workspace/TO_SERVER/var/config" - директория, где приложение пишет свои динамические конфиги
"/workspace/TO_SERVER/var/runtime" - директория служебная, для логов и файлового кеша компонентов приложения


---

Я с помощью "/workspace/ansible" настраиваю сервер. И после этого из под учётной записи bes пытаюсь с помощью WinCSP
выложить исходники на настроенный "make full-deploy" сервер и у меня не хватает прав на копирование. Я догадываюсь, что
мне надо для этого хотя бы sudo, но я не знаю как это сделать из под WinCSP. Я бы мог зайти от root, но не хочется
работать сразу из под emergency учётной записи (хотя и можно это сделать), мне кажется, должны быть более правильные
способы. Копия приложения (исходники приложения находятся в "/workspace/app") очищенная от мусора и приготовленная для
копирования на сревер находится здесь "/workspace/TO_SERVER". Примерные назначения директорий, которые должны
подразумевать назначения прав для записи приложением:
"/workspace/TO_SERVER/backend/pub" - публичный корень админки (ADM_HOST)
"/workspace/TO_SERVER/backend/pub/assets" - директория ресурсов для админки
"/workspace/TO_SERVER/frontend/pub" - публичный корень сайта (MAIN_HOST)
"/workspace/TO_SERVER/frontend/pub/assets" - директория ресурсов для сайта
"/workspace/TO_SERVER/static" - корень домена статики (FILES_HOST)
"/workspace/TO_SERVER/var/config" - директория, где приложение пишет свои динамические конфиги
"/workspace/TO_SERVER/var/runtime" - директория служебная, для логов и файлового кеша компонентов приложения
Как мне лучше копировать файлы и потом назначить нужные права всему скопированному на сервер? Впоследствии я хотел бы
все исходники приложения залить на гитхаб и разворачивать оттуда с помощью "git clone" + "composer install", но сейчас
думал просто скопировать вручную.
Или мне использовать "/workspace/ansible/playbooks/stage-5c-data-transfer.yml" или сразу заморочиться "git clone"? Какой
путь выбрать и как это лучше все сейчас сделать?
Все мои пакеты "besnovatyj/*" давно на "https://packagist.org/users/besnovatyj/packages/"
на данный момент размер vendor 214 мегабайт и там почти 18000 файлов, боюсь пофайловое копирование из-за этого не
подходит
Если что, в Ansible open_basedir закомментирован полностью и пока ни на что не влияет
---
Предложи самый правильный и корректный вариант, без скидок и упрощений, потому что сейчас твоя модель "Opus 4.8" выдала
полный бред по этому вопросу


---


Что не так с ролью data_transfer сейчас

1. Файлы лягут не туда. Роль синкает data_transfer_src → /var/www/{{ domain_name }}/, а nginx (webserver.yml) ждёт корни в /var/www/{{ domain_name           
   }}/app/frontend/pub и т.д. Дерево TO_SERVER — это содержимое app/, значит dest должен быть {{ app_root }}/app/.
2. data_transfer_writable_dirs — от старой структуры (pub/storage/runtime, pub/web/static/cache…). Актуальный список под вашу разметку:                      
   data_transfer_writable_dirs:                                                                                                                                 
   - app/backend/pub/assets                                                                                                                                   
   - app/frontend/pub/assets                                                                                                                                  
   - app/static                                                                                                                                               
   - app/var/config                                                                                                                                           
   - app/var/runtime
2. Сами backend/pub и frontend/pub на запись не нужны — только их assets.
3. Владелец кода. Сейчас роль делает chown -R www-data:www-data на всё дерево — это значит, что PHP-процесс может переписать собственный код (классический   
   усилитель любой RCE/upload-уязвимости). Правильно, и это согласуется с вашим принципом «безопасность выше удобства»: весь код — root:root, каталоги 0755 /   
   файлы 0644 (www-data только читает), и лишь writable-каталоги из списка выше — www-data:www-data.
4. vendor. В TO_SERVER его нет (там 4.8 МБ), а composer на сервере отсутствует — значит vendor должен ехать в артефакте. Хорошая новость: я проверил         
   vendor/besnovatyj — это реальные каталоги с Packagist, не симлинки path-репо, так что rsync перенесёт их корректно. 214 МБ / 18k файлов для rsync — рядовая  
   задача: первый прогон несколько минут (с -z сжатием), последующие — только дельта. Самый чистый вариант собрать артефакт — выполнить в TO_SERVER локально    
   composer install --no-dev --optimize-autoloader (dev-пакеты не поедут на прод); более быстрый компромисс — просто скопировать готовый app/vendor внутрь      
   TO_SERVER.                  

Рекомендуемый порядок действий

1. Поправить роль data_transfer: dest → {{ app_root }}/app/, новый список writable-каталогов, модель владения «код root, запись www-data точечно». Заодно    
   убедиться, что на сервере установлен rsync (ansible.posix.synchronize требует его с обеих сторон).
2. Собрать артефакт: TO_SERVER + vendor (лучше свежим composer install --no-dev).
3. data_transfer_src задать в inventory или через -e — абсолютный путь к TO_SERVER в вашей локальной среде, где запускается Ansible (не путь из моей сессии).
   Учтите data_transfer_delete: true — на сервере будет стёрто всё, чего нет в артефакте (для первой выкладки это как раз то, что нужно).
4. make stage-5c (с opt-in импортом дампа, если нужен: data_transfer_import_db=true).
5. После исходников — stage-5b (queue-воркеры, они и в Makefile помечены «только после деплоя исходников») и stage-6 verification.

Такой вариант использует уже существующий у вас контур (automation-пользователь с NOPASSWD, sudo rsync на удалённой стороне), не расшатывает права bes, и он
же останется транспортом до перехода на git-деплой.          


---

Следующий уровень (упоминаю, раз «без скидок»)

Канонический деплой — не clone-in-place, а схема releases + symlink (Deployer/Capistrano): каждая выкладка в /var/www/<domain>/releases/N, атомарное         
переключение симлинка current, мгновенный откат. Требует поменять nginx-корни на .../current/app/... и чуть усложнить роль. Для одного сервера и первой      
выкладки clone-in-place — полноценное рабочее решение, а releases/symlink стоит заложить как целевую архитектуру, когда деплой станет регулярным.            
                                                               
---

Готово. Сделал всё три части: роль composer в stage-4, отдельную release-роль с плейбуком stage-5d и make-цель release. YAML всех файлов проверен, make      
syntax-check подхватит новый плейбук автоматически (глоб stage-*.yml).

Что создано и изменено:

- roles/web-server/composer/tasks/main.yml — Composer из apt (2.7 в noble; обновления подхватит unattended-upgrades, что ложится в вашу                      
  fire-and-forget-политику). Подключён в stage-4-webserver.yml после php, в post-checks stage-4 добавлен assert composer --version.
- roles/web-server/release/ — роль релиза: git (ставится при отсутствии) → clone/pull https://github.com/besnovatyj/yii2-cms.git в /var/www/{{ domain_name }}
  → composer install --no-dev --optimize-autoloader → php init --env=Production --overwrite=n --delete=n → права записываемых каталогов. Хэндлер — graceful    
  reload php-fpm (сброс OPcache) при любом изменении кода.
- playbooks/stage-5d-release.yml — pre-flight (php-fpm активен, composer есть), роль, post-checks в вашем стиле: vendor/autoload.php и pub/index.php         
  существуют, автозагрузчик грузится под www-data, writable-каталоги реально записываемы www-data.
- Makefile — цель release (с agent-up), в full-deploy не входит.    

Ключевые решения внутри роли:

- Модель прав: весь код принадлежит ansible (деплой-пользователь), www-data только читает — PHP не может переписать собственный код. Записываемые каталоги   
  (backend/pub/assets, frontend/pub/assets, static, var/config, var/runtime) — ansible:www-data с setgid 2775: git пишет как владелец, приложение — через      
  группу. Это снимает конфликт «git не может создать файл в каталоге www-data».
- Идемпотентность init: я прочитал ваш init — при --overwrite=n существующие локальные конфиги не перетираются, а cookieValidationKey подставляется только в
  пустое значение, значит повторный релиз не сбрасывает сессии и куки. chmod 0777 из init на www-data-каталогах не пройдёт и перекрывается задачей роли —      
  сообщения «Error. Operation chmod not permitted» в его выводе ожидаемы.
- Защита от прежней выкладки: если /var/www/bes-v.ru не пуст и не git-клон (например, остатки rsync-экспериментов) — роль останавливается с понятным         
  сообщением, ничего не удаляя.

Что нужно от вас перед первым запуском:

1. Запушить репо: корень репозитория = корень проекта (с /app внутри) — именно так клон совпадает с nginx-корнями .../app/frontend/pub. Если решите пушить   
   только содержимое app/, поменяйте release_app_subdir: "" в defaults роли.
2. Проверить .gitignore: всё, что вы вычищали руками для TO_SERVER (app/tmp, app/db/backup, дампы, var/runtime содержимое), должно игнорироваться; vendor в  
   репо не нужен — его соберёт composer из Packagist. В writable-каталогах не держите трекаемых файлов тяжелее .gitkeep.
3. Прогнать make syntax-check, затем make stage-4 (доставит composer на сервер) и make release.

Замечание вне задачи: TO_SERVER/backend в середине работы перестал читаться из моей сессии (в начале был) — либо вы его сейчас перекладываете, либо это опять
drvfs; на git-деплой не влияет, но если каталог «пропал» и у вас — это знакомый симптом 9p.            


---


Исправлено. Итог разбора лога и правок:

Что случилось. Composer три минуты пытался скачать пакеты и упал: сначала на каждом дисте allow_url_fopen must be enabled in php.ini, затем на               
fallback-загрузке из исходников — The Process class relies on proc_open, which is not available. Оба ограничения пришли из вашего security.ini (роль php),   
который задача Copy PHP configuration files to cli/conf.d копировала в CLI-конфиг вместе со всеми остальными ini. Для FPM этот hardening правильный, но CLI —
это composer, php init, yii-консоль: им proc_open и https-обёртка нужны легитимно. Именно поэтому в Ubuntu у fpm и cli отдельные php.ini.

Правки:

1. roles/web-server/php/tasks/main.yml — копирование в cli/conf.d теперь пофайловое через fileglob с исключением security.ini (FPM-копирование не тронуто —  
   там hardening остаётся полностью). Плюс задача-чистильщик, удаляющая уже задеплоенный cli/conf.d/security.ini — так что полная переустановка сервера не      
   обязательна: достаточно make stage-4, затем make release.
2. playbooks/stage-4-webserver.yml — в post-checks добавлен assert php -r на proc_open + allow_url_fopen: если security.ini когда-нибудь снова попадёт в CLI,
   это поймается на stage-4 за секунду, а не трёхминутным падением composer в середине релиза.

Две попутные заметки из лога, не требующие действий: поток Deprecation Notice в stderr — это apt-шный Composer 2.7 под PHP 8.4, шумно, но безвредно (на      
логику changed_when не влияет); и скелет-удаление каталогов в начале лога корректно отработало в режиме skip — значит, клон на сервере уже есть, повторный   
make release просто обновит его.       

---

Разобрался и переставил. Полная картина второй ошибки и что изменено:

Что было в логе. Фикс CLI сработал — все 130 пакетов скачались и установились за 14 секунд. Упал уже post-install-cmd из вашего composer.json: php yii       
Modman/modules/recompile → Could not open input file: yii. Это chicken-and-egg моего порядка задач: yii-скрипт и локальные конфиги генерирует php init, а он
в роли шёл после composer.

Правка (release/tasks/main.yml, порядок задач):

1. git clone/pull
2. импорт дампа БД (opt-in) — теперь до composer
3. php init — сам он автономный, vendor ему не нужен, зато создаёт yii, *-local.php и cookie key
4. writable-каталоги — до composer, потому что recompile пишет merge-plan в var/config
5. composer install — его post-install-скрипт теперь получает готовые yii, конфиги и записываемый var/config

Я проверил по коду modman в vendor: реестр модулей — файловый (var/config/modules-state.php, @config-dyn-gen), БД для recompile не нужна, так что порядок    
закрывает проблему полностью.

Важный heads-up на следующий шаг. В репозитории var/ содержит только runtime — var/config/modules-state.php (реестр установленных модулей) на чистый сервер  
не приезжает. ModuleRegistry при отсутствии lock-файла молча стартует с пустым реестром, то есть первый релиз пройдёт зелёным, но recompile соберёт          
merge-plan без единого активного модуля — сайт поднимется «голым ядром». Судя по коду вашего ModulesController, штатное восстановление — php yii             
Modman/modules/sync (пересборка реестра из каталога composer + истории миграций, которая приедет с дампом БД). Рекомендую после первого make release-db      
выполнить sync на сервере разово; если скажете — добавлю его в роль опциональным шагом (не делаю сам, потому что семантика adopt/sync — ваша предметная      
область: не хочу молча активировать лишние модули).       

---

Готово, можно повторять make release. Разбор третьей ошибки:

Что в логе. Прогресс: vendor уже на месте («Nothing to install»), init отработал, но post-install-скрипт упал глубже — php yii Modman/modules/recompile не   
смог даже забутстрапиться: Failed opening required '/var/www/bes-demo.ru/var/config/merge-plan.php'. Это замкнутый круг именно первого релиза: yii собирает  
конфиг через ConfigFactory по merge-plan, а merge-plan генерирует тот самый recompile, который мы пытаемся запустить. На dev это никогда не проявлялось — там
артефакты лежат в var/config годами.

Решение — «посев» var/config с dev-машины, по той же модели, что и дамп БД: реестр модулей и merge-plan — это состояние приложения, а не код, и на чистый    
сервер оно должно приехать один раз извне. Прежде чем делать, я проверил корректность подхода:

- merge-plan.php и modules-state.php портабельны — все пути в них package-relative (besnovatyj/... => src/config/adminMenu.php), разрешаются через           
  ConfigPaths(@root, '', 'vendor'), абсолютных dev-путей нет;
- dev-реестр чистый: только боевые модули, debug-panel-modules из require-dev ни в реестр, ни в merge-plan не входит — на prod с --no-dev конфликтов не      
  будет.

Правки:

1. release/defaults/main.yml — release_var_config_seed_src с дефолтом на app/var/config вашего проекта (соседний с ansible каталог) — руками ничего          
   копировать не надо.
2. release/tasks/main.yml — перед composer: если на сервере нет merge-plan.php, все *.php из локального var/config копируются на сервер (ansible:www-data,   
   0664). Ключевая семантика — посев только один раз: пока merge-plan на сервере существует, живое var/config (пересборки recompile, опции модулей из админки)  
   никогда не перезаписывается. Если локальный источник пуст — внятный assert вместо загадочного падения recompile.
3. stage-5d-release.yml — var/config/merge-plan.php добавлен в post-check артефактов.

Бонус: посев привозит и modules-state.php, так что мой прошлый heads-up про «голое ядро» и ручной Modman/modules/sync снимается — реестр приедет заполненным,
recompile в post-install пересоберёт артефакты уже из него.  

---

Готово. Что сделано и как этим пользоваться:

1. Лишние www убраны

В ansible/inventory/group_vars/all/webserver.yml у backend и static теперь только основные имена:

server_name: "{{ app_hosts.adm }}"                                                                                                                                             
server_name: "{{ app_hosts.files }}"

У frontend www.bes-demo.ru оставлен — он в DNS есть. Certbot строит -d из этой же переменной, так что для выпуска сертификата больше ничего не нужно. Но nginx-конфиги на      
сервере уже отрендерены со старыми server_name (их рендерит роль nginx в stage-4) — это безвредно, однако для синхронизации стоит перегнать make stage-4 перед 5a.

2. --staging — уже был, добавил только удобство

Флаг certbot_staging уже реализован в роли, менять код не пришлось. Я добавил три вещи:

- make stage-5a-staging — прогон против staging CA (не жжёт прод-квоты, сертификат недоверенный).
- make stage-5a-force — новый флаг certbot_force_renewal в роли. Он нужен из-за неочевидной ловушки: после успешного staging-теста в /etc/letsencrypt/live/ лежит валидный     
  неистёкший staging-сертификат, и обычный make stage-5a из-за --keep-until-expiring его не заменит — просто переустановит в nginx, и браузер продолжит ругаться. Force-запуск   
  разово перевыпускает у боевого CA.
- Комментарий в defaults/main.yml роли, объясняющий этот сценарий.

Рабочий порядок, если хочешь сначала проверить:

make stage-4            # обновить server_name в nginx                                                                                                                         
make stage-5a-staging   # проверка DNS/пайплайна без расхода квот                                                                                                              
make stage-5a-force     # разовый боевой выпуск поверх staging-серта

Дальше по жизни — обычный make stage-5a.

К слову: прямо сейчас сертификата на сервере нет вообще (выпуск упал целиком), а валидацию не прошли только два www-домена — остальные четыре LE проверил без претензий. Так   
что после правки www можно и сразу make stage-4 && make stage-5a без staging-этапа — риск минимальный. Staging-путь оставлен на случай, если хочешь перестраховаться или ещё   
будешь крутить DNS.                 

---

























