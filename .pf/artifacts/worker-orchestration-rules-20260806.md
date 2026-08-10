# Worker Orchestration Rules

## Metadata

- created_at: 2026-08-06T07:48:24+04:00
- scope: future code-writing assignments in this ProcessForge project
- owner_role: Codex orchestrator / architect
- product_code_changed: no

## Delegation Rule

For code-writing work, the main agent acts as orchestrator and architect. Code implementation tasks must be delegated to ProcessForge shell-workers with bounded, non-overlapping file scopes.

These workers are not Codex sub-agents. They must be launched and tracked through the ProcessForge worker/orchestrator shell-worker flow.

## Required Worker Runtime

- requested_model: `gpt-5.3-codex-spark`
- default_reasoning_effort: `high`
- raise reasoning effort when the task risk justifies it.

If the active runner does not expose `gpt-5.3-codex-spark`, the orchestrator must not silently substitute another model for code-writing work. The limitation must be recorded in the session artifact/log before proceeding.

## Mandatory IDE/MCP Policy

ProcessForge shell-workers must use MCP PHPStorm first for project code and file work.

Minimum worker evidence:

- state whether MCP PHPStorm was available;
- list the PHPStorm MCP actions used for code/file inspection or edits;
- if MCP PHPStorm is unavailable, stop or request orchestrator approval for fallback;
- record any approved fallback in `.pf/runtime/telemetry/` or the current `.pf/logs/` artifact.

Serena and shell tools remain fallback/supporting tools only after the PHPStorm MCP attempt is recorded.

## Local Runtime Waiver: PowerShell Fallback

Effective from: 2026-08-06T09:45:00+04:00

For the current run `linked-otpravka-select-fields-20260806`, the user explicitly approved PowerShell fallback for implementation workers because `codex-exec` does not currently prove callable PHPStorm MCP tools inside the worker session.

This waiver is narrow:

- applies only to `t02-library-fields-assets` and `t03-plugin-ajax-endpoints` follow-up implementation attempts;
- does not remove the requirement to inspect local docs and existing project patterns before changes;
- does not allow editing outside `allowed_files`;
- does not allow touching `forbidden_files`;
- does not allow destructive Git or filesystem commands;
- requires a detailed worker report with changed files, commands run, tests/checks, and residual risks;
- requires orchestrator review before any implementation is accepted.

The worker must still record whether PHPStorm MCP was available/callable. If it is not callable, the worker may proceed with PowerShell under this waiver only for its assigned scope.

## Orchestrator Responsibilities

- create or select the active ProcessForge assignment before implementation;
- split implementation into disjoint ProcessForge shell-worker scopes;
- enforce one writer per file scope;
- review worker output before integration;
- run or delegate assurance after implementation;
- keep `.pf` artifacts, logs, reviews, and handoffs current.
