# artifact-policy

Нормализует артефактную модель задачи перед выполнением стадий.

## Responsibilities

- выбрать обязательные artifact templates по stage
- проверить наличие required artifacts
- создать missing artifacts из templates
- зафиксировать artifact ownership и locations
- обновить task log

## Entry Criteria

- есть task intent
- определён target stage или mode

## Exit Criteria

- все обязательные артефакты stage существуют
- известны artifact locations
- orchestrator может валидировать handoff
