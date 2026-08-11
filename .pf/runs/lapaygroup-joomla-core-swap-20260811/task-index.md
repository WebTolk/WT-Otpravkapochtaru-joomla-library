# Task Index: lapaygroup-joomla-core-swap-20260811

| Task | Role | Model | Writes | Output |
| --- | --- | --- | --- | --- |
| T13 | stand snapshot and autoload map | `gpt-5.3-codex-spark` | `.pf/artifacts`, `.pf/tmp` only | `worker-lapaygroup-core-swap-stand-snapshot-20260811.md` |
| T14 | Joomla core/vendor SDK swap writer | `gpt-5.3-codex-spark` | `joomla.local` vendor/classloader + backups | `worker-lapaygroup-core-swap-writer-20260811.md` |
| T15 | SDK transport and read-only API smoke | `gpt-5.3-codex-spark` | `.pf/artifacts`, `.pf/tmp` only | `worker-lapaygroup-core-swap-sdk-smoke-20260811.md` |
| T16 | JoomShopping/admin surface check | `gpt-5.3-codex-spark` | `.pf/artifacts`, `.pf/tmp` only | `worker-lapaygroup-core-swap-joomshopping-surface-20260811.md` |
| T17 | evidence reviewer | `gpt-5.3-codex-spark` | `.pf/artifacts`, `.pf/runtime/telemetry` only | `reviewer-lapaygroup-core-swap-20260811.md` |

T14 must run after T13. T15 and T16 may run after T14. T17 must run after T15
and T16.
