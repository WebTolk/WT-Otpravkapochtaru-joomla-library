# Plan: LapayGroup Thin Wrapper Implementation

Date: 2026-08-11

## Goal

Move the package toward the WT Max architecture:

- Composer/GitHub build pulls upstream `lapaygroup/russianpost`.
- Joomla package ships a thin Joomla wrapper plus Joomla Form fields/assets.
- Forked SDK code is removed from the product source after the wrapper/build path is in place.
- System plugin settings compatibility is preserved.

## Worker Waves

Wave 1, parallel:

- T34 Build and CI writer.
- T35 Runtime wrapper writer.

Wave 1B, after Wave 1 orchestrator review:

- T35B Runtime fork-dependency cleanup writer.

Wave 1C, after orchestrator review of T34/T35B:

- T34B Build tracking corrective writer.
- T35C Runtime contract corrective writer. This worker failed after partial edits.
- T35D Runtime entity hydration corrective writer to finish T35C's incomplete runtime patch.

Wave 2, after Wave 1C:

- T36 Fork prune and manifest writer.
- T36B Fields/plugin import corrective writer, required after T36 review found stale deleted-namespace imports.
- T37 Docs and tests writer. This worker failed after partial edits.
- T37B Docs/tests corrective writer to finish and fix T37's incomplete docs/tests.

Wave 3:

- T38 Implementation reviewer.

## Tool Requirement

Workers must use PHPStorm MCP for repository navigation and file inspections where available. Shell is allowed for Composer, package, Git, and Joomla CLI checks.

## Wave 1 Review Note

T35 left the runtime facade depending on old fork classes under `Configuration`, `Entity`, and `Exception`. T35B was added so T36 can later remove forked SDK files safely.

## Wave 1C Review Note

Orchestrator review found that `build/release.php` was still hidden by `.gitignore`, and PHPStorm reported runtime type mismatches where the wrapper passes generic objects into upstream LapayGroup entity parameters. T34B and T35D must finish before T36 starts.

## Wave 2 Review Note

T36 removed old fork namespaces, but fields and the system plugin still imported deleted `Configuration`/`Exception` classes. T36B must finish before docs/tests and final review.

## Wave 2B Review Note

T37 failed after partial docs/tests edits. Orchestrator review found stale README examples for deleted fork classes and an incorrect architecture test root/path check. T37B must finish before final review.
