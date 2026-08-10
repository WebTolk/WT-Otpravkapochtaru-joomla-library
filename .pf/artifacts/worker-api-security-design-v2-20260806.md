Готово. Артефакт создан: [`.pf/artifacts/worker-api-security-design-v2-20260806.md`](D:/Dev/WT-Otpravkapochtaru-joomla-library/.pf/artifacts/worker-api-security-design-v2-20260806.md).

Содержимое включает:

1. `mcp_evidence` — фиксируется локальный анализ через PowerShell и отсутствие использования PHPStorm MCP с согласованным fallback.
2. `api_source_map` — подтверждённый источник для OPS (`OpslistField` → `getShippingPoints()` → `/1.0/user-shipping-points`) и указание отсутствия подтверждённого источника для category/type в текущем scope.
3. `ajax_security_contract` — отмечено, что OPS path не AJAX; для category/type заданный контракт не определён.
4. `implementation_gate_decision` — решение: блокировка (BLOCK) до явного источника category/type.

Если нужно, следующим шагом могу сразу подготовить шаблон безопасного `ajax_security_contract` для category/type после расширения scope на файлы, где реализован их источник.