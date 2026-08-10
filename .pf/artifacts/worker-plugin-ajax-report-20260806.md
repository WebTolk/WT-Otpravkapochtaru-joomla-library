Задача выполнена в рамках выданного скоупа.
Отчёт сформирован: `.pf/artifacts/worker-plugin-ajax-report-20260806.md`

Содержимое отчёта включает:
1. `changed_files`
2. `security_notes`
3. `unit_tests`

Ключевое:
- Добавлен обработчик `com_ajax` в `plg_system_wt_otpravkapochtaru/src/Extension/WtOtpravkapochtaru.php`.
- Вынесена логика опций в новый сервис `plg_system_wt_otpravkapochtaru/src/Service/AjaxShippingOptionsService.php`.
- Добавлены локализованные сообщения ошибок в `en-GB` и `ru-RU`.
- Добавлены unit-тесты в `tests/Unit/PluginAjax/AjaxShippingOptionsServiceTest.php`.
- Прогон тестов на этом этапе не выполнялся (сообщено в отчёте).