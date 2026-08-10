# Review: PHPStorm MCP runtime diagnostic

Date: 2026-08-06
Task: `t05-phpstorm-mcp-runtime-diagnostic`
Worker model: `gpt-5.3-codex-spark`
Reasoning: `high`

## Verdict

Accepted for cause analysis with delivery caveats.

The diagnostic worker confirmed the same boundary observed by the orchestrator:

- PHPStorm is configured in metadata/workspace access.
- The endpoint `http://127.0.0.1:64442/stream` responds with HTTP `200`.
- The current `codex-exec` worker path does not prove callable PHPStorm MCP tool availability inside the worker session.
- Previous implementation workers used shell operations for file/code work instead of PHPStorm MCP.
- Do not relaunch `t02` or `t03` through the same `codex-exec` path while the MCP-first rule is mandatory.

## Evidence

Worker run status:

- `t05-phpstorm-mcp-runtime-diagnostic`: completed
- `exit_code`: `0`
- report path: `.pf/artifacts/phpstorm-mcp-runtime-diagnostic-20260806.md`

Runtime logs show:

- workspace metadata includes `mcp: phpstorm, serena`;
- `workspace-access.json` includes PHPStorm registry data and `runtime_note: Requires PhpStorm MCP endpoint at 127.0.0.1:64442`;
- direct endpoint check returned `200`;
- the worker still used shell commands for diagnostics and writing the allowed report artifact;
- no actual PHPStorm MCP project operation was recorded.

Previous implementation worker logs show:

- `t02` used `Get-Content`, `New-Item`, `Set-Content`;
- `t02` created `tests/temp_write_probe.txt`;
- `t02` attempted PHP/XML writes through PowerShell;
- `t03` used `rg` and `Get-Content`;
- no accepted PHPStorm MCP code/file operation was recorded.

## Delivery Caveats

The worker expected report artifact was created, but the collected file is mojibake and contains a short final-response style summary instead of the full requested report structure.

The fuller structured diagnostic text is visible in the worker stderr immediately before the artifact write command. Because of this, the worker output is useful as runtime evidence, but the orchestrator review remains the readable acceptance record.

## Cause Analysis

The break is not the port and not the ProcessForge metadata.

Observed chain:

1. `.codex/config.toml` points PHPStorm MCP to `127.0.0.1:64442`.
2. HTTP check to `127.0.0.1:64442/stream` returns `200`.
3. ProcessForge assignment/capsule/workspace-access metadata lists `phpstorm`.
4. `codex-exec` launches a shell-based Codex worker.
5. Worker sessions do not demonstrate callable PHPStorm MCP project operations.
6. Workers fall back to shell commands for file/code work.

Therefore the likely cause is a runtime injection/driver boundary: `codex-exec` carries MCP requirements as metadata but does not currently enforce or prove that PHPStorm MCP tools are callable inside the worker session.

## Rerun Policy

Implementation tasks `t02-library-fields-assets` and `t03-plugin-ajax-endpoints` must remain blocked for relaunch through the same path.

Allowed next runtime options:

- introduce or select a PF worker driver that exposes callable PHPStorm MCP tools inside the worker session and logs those calls;
- use a manual/IDE-backed worker mode with explicit PHPStorm evidence;
- or explicitly change the orchestration policy to allow shell fallback before implementation resumes.

Until one of those is selected, no further implementation worker should write product code.
