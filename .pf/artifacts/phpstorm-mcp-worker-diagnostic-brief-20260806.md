# Worker Brief: PHPStorm MCP runtime diagnostic

Task: `t05-phpstorm-mcp-runtime-diagnostic`
Model: `gpt-5.3-codex-spark`
Reasoning: `high`

## Objective

Diagnose whether a ProcessForge shell-worker launched through `codex-exec` can actually use callable MCP PHPStorm tools for repository code/file work.

This is a runtime diagnostic task only. Do not implement linked select fields. Do not edit product code.

## Required Checks

1. Inspect the worker's actual tool surface.
   - Determine whether a callable PHPStorm MCP tool is available in this worker session.
   - Record exact evidence: tool names, server/tool discovery output, or exact failure/error text.
   - Do not treat workspace metadata that merely lists `phpstorm` as sufficient evidence.

2. Test PHPStorm MCP access.
   - If a PHPStorm MCP tool is callable, use it for a harmless read-only project operation such as listing/opening the project or reading a known assignment file.
   - If no PHPStorm MCP tool is callable, stop code/file work and explain the missing capability.

3. Compare metadata versus runtime.
   - Check `.codex/config.toml`.
   - Check `.pf/artifacts/worker-launch-control-review-20260806.md`.
   - Check `.pf/artifacts/worker-orchestration-rules-20260806.md`.
   - Check the previous worker stderr logs for `t02` and `t03`.
   - Check the current worker's `workspace-access.json` if available.

4. Identify the likely cause.
   - Separate these states:
     - PHPStorm endpoint/port reachable or not.
     - PHPStorm registered in ProcessForge workspace metadata or not.
     - PHPStorm MCP listed in worker capsule/workspace access or not.
     - PHPStorm MCP actually callable as a tool from inside `codex-exec` or not.
   - Explain exactly where the chain breaks.

5. Recommend rerun policy.
   - Say whether implementation workers may be relaunched through the same `codex-exec` driver.
   - If not, state the minimal runtime change or launch mode needed before `t02` and `t03` can resume.

## Allowed Output

Write only:

- `.pf/artifacts/phpstorm-mcp-runtime-diagnostic-20260806.md`

## Forbidden

- Do not change product code.
- Do not modify `lib_webtolk_otpravkapochtaru/`, `plg_system_wt_otpravkapochtaru/`, `tests/`, `composer.json`, or package/build files.
- Do not continue into implementation after diagnosis.
- Do not claim PHPStorm MCP was used unless a callable PHPStorm MCP action was actually executed and recorded.

## Expected Report Structure

The report must include:

- `Summary`
- `Tool surface evidence`
- `PHPStorm MCP read-only test`
- `Metadata versus runtime`
- `Cause analysis`
- `Rerun recommendation`
- `Residual risks`
