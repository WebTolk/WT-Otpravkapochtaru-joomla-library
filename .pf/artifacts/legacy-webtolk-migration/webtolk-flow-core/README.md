# `.webtolk` Development Flow Pack

Этот пакет копируется в проект как готовая операционная среда для artifact-driven development flow.

## Назначение

- platform-agnostic и language-agnostic core flow
- stage-based execution: `investigation`, `implementation`, `assurance`, `release`, `evolve`
- MCP-first tool routing через registry и policy
- полные шаблоны артефактов, контракты skill-ов и logging/telemetry
- patch → evolve → rules update learning loop

## Структура

- `config/` runtime-конфиги, схемы, MCP registry, telemetry schema
- `context/` проектный контекст и overlay hooks
- `rules/` иерархия правил и override policy
- `skills/` core skill pack с contract-driven handoff
- `templates/` шаблоны всех обязательных артефактов и логов
- `patches/` рабочие patch-артефакты и процесс patch pipeline
- `evolutions/` learning loop, cursor, evolution reports
- `logs/` журналирование задач, агентов и проверок
- `extensions/` модель расширений и примеры overlay

## Tool Resolution Model

- logical tool name = WHAT should be invoked in the current stage, for example `phpstan`
- resolves_to = WHERE runtime resolves the tool inside shared `tools/` or `toolchains/`
- execution.strategy = HOW the tool is invoked, for example `configured-toolchain`
- execution.command_name = concrete command identity passed to the selected strategy

## Загрузка

1. Читать `AGENTS.md`.
2. Загрузить `config/config.yaml`.
3. Применить `rules/axioms.md`, затем `rules/base.md`.
4. Подключить overlays из `context/project-context.yaml`, `extensions/`, `rules/platform/`, `rules/toolchain/`, `rules/domain/`, `rules/tooling/`.
5. Выполнить `skills/flow-orchestrator` для выбора стадии и проверки артефактов.

## Обязательные каталоги runtime

- `patches/` хранит patch proposals и approved patch records
- `evolutions/` хранит cursor и evolution reports
- `logs/` хранит task/agent/verification logs

## Быстрый старт

1. Скопировать `.webtolk` в корень проекта.
2. Скопировать `context/project-context.template.yaml` в `context/project-context.yaml`.
3. Заполнить project context.
4. Запустить intake через шаблон `templates/artifacts/brief.template.md`.
5. Дальше выполнять стадии через `skills/flow-orchestrator`.

## Context Templates

- Generic bootstrap: `context/project-context.template.yaml`
- Joomla extension bootstrap: `context/joomla-extension.project-context.template.yaml`
