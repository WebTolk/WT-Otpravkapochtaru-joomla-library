# Logging Policy

## When To Log

- после смены stage
- после создания или обновления артефакта
- после file edits
- после assurance activity
- после tool fallback
- после patch/evolution update

## Minimum Record

- timestamp
- task
- files
- tools
- status
- risks

## Status Vocabulary

- `started`
- `in_progress`
- `completed`
- `blocked`
- `failed`

## Risk Vocabulary

- `none`
- `low`
- `medium`
- `high`
- `critical`

## File Rules

- `task-log.md` tracks stage progression
- `agent-log.md` tracks substantive actions and handoffs
- `verification-log.md` tracks checks and evidence
- `tool-telemetry.ndjson` stores machine-readable tool events using `config/tool-telemetry.schema.json`
