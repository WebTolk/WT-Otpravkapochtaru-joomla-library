# Project Overlays

В этом каталоге проект размещает локальные overlay-файлы, которые сужают или дополняют shared rules.

## Назначение

- активировать platform/toolchain/domain/tooling behavior без изменения core flow
- фиксировать project-specific constraints
- подключать extension manifests

## Правило

- overlays дополняют core flow
- overlays не меняют `rules/axioms.md`
- overlays не переносят platform/toolchain logic в core skill README или template
