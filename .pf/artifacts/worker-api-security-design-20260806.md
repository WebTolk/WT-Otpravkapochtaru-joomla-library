Готово. Я ограничился назначенным чтением и подготовил артефакт:

- `.pf/artifacts/worker-api-security-design-20260806.md`

В нём выполнены оба обязательных блока:
- `api_source_map`
- `ajax_security_contract`

Ключевые выводы: OPS имеет явный источник в `getShippingPoints() -> /1.0/user-shipping-points`; для `shipment_type`/`shipment_category` в доступном коде есть только параметры `mailType` и `mailCategory` (фильтры в `getAllBatches`), но нет отдельного локального справочника/endpoint для их перечисления — это отмечено как gap и предложен контрактный источник через безопасный `com_ajax`-контракт.