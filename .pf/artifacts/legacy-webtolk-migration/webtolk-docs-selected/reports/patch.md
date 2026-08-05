# Patch

## Patch Id
PATCH-20260422-1800-public-surface-prune

## Source Task
- `Remove dead donor-era public methods and close the cleanup cycle`

## Problem Or Learning
- The facade still exposed four donor-era methods with no confirmed live contract.
- Keeping them as explicit unsupported methods preserved historical noise in the public API and in the verification baseline.
- Once donor backward compatibility was ruled out, removal became the correct product decision.

## Proposed Reusable Change
- Treat unsupported stubs only as a temporary diagnostic state.
- When a new library is not donor-compatibility-bound, remove dead public methods after live verification confirms they have no current contract.
- Keep the read-only verification runner aligned with the real public facade.

## Target Layer
- Project public API boundary, verification runner, and cycle-close artifacts.

## Files To Update
- `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php`
- `lib_webtolk_otpravkapochtaru/src/Exception/UnsupportedEndpointException.php`
- `.webtolk/tmp/verify/joomla-local-api-sweep.php`
- `.webtolk/tmp/verify/joomla-local-unsupported-probe.php`
- `docs/reports/release-notes.md`
- `docs/reports/migration-notes.md`
- `docs/reports/patch.md`
- `docs/reports/evolution-report.md`
- `.webtolk/patches/patch-20260422-1800-public-surface-prune.md`
- `.webtolk/evolutions/cursor.json`
- `.webtolk/logs/task-log.md`
- `.webtolk/logs/agent-log.md`
- `.webtolk/logs/verification-log.md`

## Compatibility Considerations
- Public facade signature intentionally changes by removal of dead donor-era methods.
- The library becomes less donor-shaped but more honest about the verified live surface.

## Approval Status
- Approved for cycle closure after browser verification on `joomla.local` reached `16 ok / 0 error / 14 skipped`.
