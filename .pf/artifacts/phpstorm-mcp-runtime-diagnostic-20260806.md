Результат сохранён в [`.pf/artifacts/phpstorm-mcp-runtime-diagnostic-20260806.md`](C:\Dev\WT-Otpravkapochtaru-joomla-library\.pf\artifacts\phpstorm-mcp-runtime-diagnostic-20260806.md).

Ключевое:
- `phpstorm` есть в metadata и `workspace-access`, endpoint `127.0.0.1:64442` доступен (`200`).
- реальный разрыв — не в конфиге/endpoint, а в runtime `codex-exec shell-driver`: прошлые workers не вызывали PHPStorm MCP, а делали shell-операции (`Get-Content`, `rg`, `Set-Content`, `New-Item`), что зафиксировано в stderr и control-artifacts.
- документально рекомендовано: не перезапускать t02/t03 через тот же shell-путь до внедрения callable-режима phpstorm MCP или согласованной смены режима запуска.

Хотите, дальше добавлю конкретный короткий чек-лист для оркестратора на следующий запуск (`acceptance gate` + required telemetry).