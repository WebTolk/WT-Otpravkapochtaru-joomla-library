# Patch

## Patch Id
PATCH-20260422-1730-nontracking-remediation-cycle

## Source Task
- `Close the non-tracking endpoint remediation cycle`

## Problem Or Learning
- The non-tracking facade contained a mix of donor-stale mappings, local regressions and unverified newer mappings.
- The cycle reached a clean browser-verified read-only baseline, but some donor-era methods still lack confirmed current live contracts.
- `getCountryList()` needed a stable product-level replacement because the official documentation does not confirm a current public country endpoint.

## Proposed Reusable Change
- Treat live browser sweep evidence as the canonical contract check for non-tracking transport behavior.
- Separate donor functional parity from donor literal endpoint parity.
- Use local official reference dictionaries when runtime reference endpoints are dead or absent and the product still needs stable catalog data.

## Target Layer
- Project stage logs, cycle-close artifacts, and local reference-data handling.

## Files To Update
- `docs/reports/release-notes.md`
- `docs/reports/migration-notes.md`
- `docs/reports/patch.md`
- `docs/reports/evolution-report.md`
- `.webtolk/evolutions/cursor.json`
- `.webtolk/logs/task-log.md`
- `.webtolk/logs/agent-log.md`
- `.webtolk/logs/verification-log.md`
- `docs/briefs/development-flow-bootstrap.md`
- `docs/reports/development-scope-bootstrap.md`

## Compatibility Considerations
- No public facade signature changes.
- Release is partial-compatible: unsupported mappings remain outside the verified live surface.

## Approval Status
- Approved for cycle closure after browser verification on `joomla.local` reached `16 ok / 0 error / 18 skipped`.
