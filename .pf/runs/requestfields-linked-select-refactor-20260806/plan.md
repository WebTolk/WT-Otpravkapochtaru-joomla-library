# Requestfields Linked Select Refactor Run

## Goal

Move linked select dependency configuration to a generic `requestfields` mapping and preserve the `OPS -> type -> category` chain.

## Worker

- id: `t06-requestfields-linked-select-refactor`
- shell worker: `shell-worker-requestfields-linked-select`
- model: `gpt-5.3-codex-spark`
- reasoning: `high`
- runtime driver: `.pf/runtime-drivers/codex-exec-workspace-write.yaml`

## Scope

The worker owns only the PHP field dependency metadata layer, the linked-select JS asset, related field tests, and its own report artifact.

The orchestrator owns review, integration, package build, Joomla.local runtime verification, and the separate WAM render-order blocker.
