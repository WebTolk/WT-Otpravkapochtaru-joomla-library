# Patches

`patches/` хранит patch artifacts, которые связывают task-local learning с reusable package updates.

## Flow

1. Создать patch из `templates/artifacts/patch.template.md`.
2. Привязать patch к release artifacts и logs.
3. Передать patch в `skills/evolve`.
4. После evolution обновить reusable layer и cursor.

## Naming

- `patch-YYYYMMDD-HHMM-<slug>.md`
