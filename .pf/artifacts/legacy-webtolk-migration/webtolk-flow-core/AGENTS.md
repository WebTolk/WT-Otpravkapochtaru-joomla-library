# AGENTS.md

## Mission

Этот пакет управляет production-ready development flow внутри проекта через артефакты, stage-gates и MCP policy.

## Execution Order

1. Load `config/config.yaml`.
2. Load `rules/axioms.md`.
3. Load `rules/base.md`.
4. Load overlays from project context and extension registry.
5. Resolve current stage with `skills/flow-orchestrator`.
6. Enforce tool policy from MCP registry before reading, editing, browsing or shell fallback.

## Core Invariants

- Core flow не содержит platform-specific, language-specific и toolchain-specific логики.
- Platform, toolchain и domain knowledge подключаются только через context, rules и extensions.
- Каждый stage produces artifacts before handoff.
- Следующий skill не запускается без проверки required artifacts и handoff contract.
- Code reading выполняется через MCP-first policy.
- Symbol analysis выполняется через Serena-first policy.
- Browser verification выполняется через DevTools-first policy.
- Shell допускается только как fallback с зафиксированной причиной.
- Каждое существенное действие логируется.
- Каждый fallback tool run записывается в telemetry.
- Patch loop завершает работу через evolution и обновление rules/context/extensions при наличии устойчивого знания.

## Required Files

- `config/config.yaml`
- `config/mcp-registry.global.yaml`
- `config/mcp-registry.project.yaml`
- `context/project-context.yaml` или `context/project-context.template.yaml` как bootstrap
- `logs/`
- `patches/`
- `evolutions/cursor.json`

## Stage Policy

- `investigation`: понять систему, источник проблемы, поверхность воздействия и неизвестные.
- `implementation`: выполнить изменения только после scope/domain/architecture readiness.
- `assurance`: проверить код, риски, тесты, browser/runtime effects.
- `release`: оформить поставку, release artifacts и migration notes.
- `evolve`: извлечь устойчивое знание, оформить patch/evolution и обновить reusable layers.

## Artifact Contract Rule

Если обязательный артефакт отсутствует, агент создаёт его из шаблона до продолжения стадии.
