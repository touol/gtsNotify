Пример разработки CMP для MODX на основе getTables!

gtsNotify - это редактируемые связанные таблицы под вопросы клиентов и ответы на них по каким-то сделкам.
Состоит из 3 таблиц:
Фирмы в 1 табе,
Сделки и подтаблица к ней с вопросами и ответами во втором табе. 

Перед установкой установить pdoTools и getTables.
## Для разработчиков
Имеют значения лишь 3 файла:

\core\components\gtsnotify\model\schema\gtsnotify.mysql.schema.xml
с разметкой таблиц, стандартной для MODX,
_build\elements\menus.php меню компонента с gts-конфигом gtsnotify_admin,
_build\elements\settings.php в нем забивается в системные параметры MODX gts-конфиг gtsnotify_admin.

