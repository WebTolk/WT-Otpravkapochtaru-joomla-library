# Evolution Report

## Source Patch

- `docs/reports/patch.md`
- `.webtolk/patches/patch-20260422-1800-public-surface-prune.md`

## Learning Extracted

- Live browser sweeps against the installed Joomla stand are the strongest local source of truth for non-tracking endpoint compatibility.
- Unsupported public stubs are only a transitional state; if donor compatibility is not required, dead methods should be removed instead of being kept forever as explicit gaps.

## Classification

- task-local; no reusable shared-layer update required.
- The extracted rule is project-level only: keep the public surface aligned with verified live contracts.

## Target Reusable Layer

- Explicitly rejected in this cycle: no update to shared reusable layers (`rules`, `templates`, `extensions`, `tools`).

## Changes Applied

- Removed the dead donor-era methods from the public facade.
- Registered the clean `16 ok / 0 error / 14 skipped` browser baseline as the release evidence for the reduced public surface.
- Closed the cleanup cycle through `release` and `evolve`.

## Cursor Update

- `.webtolk/evolutions/cursor.json`:
  - `last_patch_id` set to `PATCH-20260422-1800-public-surface-prune`
  - `last_evolution_id` set to `EVO-NOUPDATE-20260422-1805`
  - `updated_at` set to `2026-04-22T18:05:00+04:00`

## Follow-Up

- No mandatory follow-up remains for the removed dead methods.
- A future cycle may still re-enable mutation-path verification if write-path assurance is requested.

## Toolchain Contract References

- Browser sweep wrapper on `joomla.local`

## 2026-07-11 Status Refresh Decision

- No new product-level reusable rule was extracted from this status-only pass.
- The only correction was project-local routing of Joomla knowledge to the actual `D:/.agents/...` paths; shared contracts remain externally owned and were not modified.
- No patch or evolution cursor update is required for this pass.
