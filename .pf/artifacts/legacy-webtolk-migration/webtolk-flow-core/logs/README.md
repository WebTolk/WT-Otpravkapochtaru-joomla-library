# Logs

## Logging Policy

Каждое существенное действие должно логироваться.

## Required Fields

- `timestamp`
- `task`
- `files`
- `tools`
- `status`
- `risks`

## Files

- `logging-policy.md`
- `task-log.md`
- `agent-log.md`
- `verification-log.md`
- `tool-telemetry.ndjson`

## Telemetry

Каждый fallback tool run и каждая browser/runtime verification должны попадать в telemetry.
