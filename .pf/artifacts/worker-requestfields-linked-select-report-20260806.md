Готово. Требуемый артефакт создан:

[.pf/artifacts/worker-requestfields-linked-select-report-20260806.md](D:\Dev\WT-Otpravkapochtaru-joomla-library\.pf\artifacts\worker-requestfields-linked-select-report-20260806.md)

Что изменено:
- Перевёл контракты linked-select на `requestfields` в PHP:
  - [lib_webtolk_otpravkapochtaru/src/Fields/LinkedSelectField.php](D:\Dev\WT-Otpravkapochtaru-joomla-library\lib_webtolk_otpravkapochtaru\src\Fields\LinkedSelectField.php:73)
  - [lib_webtolk_otpravkapochtaru/src/Fields/MailtypesField.php](D:\Dev\WT-Otpravkapochtaru-joomla-library\lib_webtolk_otpravkapochtaru\src\Fields\MailtypesField.php:52)
  - [lib_webtolk_otpravkapochtaru/src/Fields/MailcategoriesField.php](D:\Dev\WT-Otpravkapochtaru-joomla-library\lib_webtolk_otpravkapochtaru\src\Fields\MailcategoriesField.php:52)
- Обновил JS runtime на чтение карты зависимостей и общий механизм сборки параметров запроса, с fallback на legacy-атрибуты:
  - [plg_system_wt_otpravkapochtaru/media/js/linked-select-fields.js](D:\Dev\WT-Otpravkapochtaru-joomla-library\plg_system_wt_otpravkapochtaru\media\js\linked-select-fields.js:10)

Статус верификаций:
- `phpstorm_mcp_evidence`: MCP-инструменты из `workspace_access.json` в этой сессии не вызывались.
- Тесты/проверки не запускались (по умолчанию в этой сессии тестирование не выполнялось без отдельного запроса).

Могу запустить целевой `phpunit` для `tests/Unit/Fields/*`, если нужно.