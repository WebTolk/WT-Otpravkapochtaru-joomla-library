# Run Plan: release-readiness-audit-20260811

## Goal

Audit the current release-readiness state after moving Joomla Form field assets
to the library package and after raising package requirements to PHP 8.3.0.

This run must not change product code. Workers are intentionally narrow and
dumb. Each worker owns exactly one report artifact. The reviewer runs only
after all worker reports exist.

## Worker Policy

- All implementation/check workers use `gpt-5.3-codex-spark`.
- The reviewer also uses `gpt-5.3-codex-spark`.
- Workers may read project files and local Joomla documentation.
- Workers may write only their assigned `.pf/artifacts/*.md` report.
- No product-code writes.
- No Joomla stand writes.
- No raw secrets in artifacts.

## Task Order

1. T25: requirements consistency audit.
2. T26: package archive and manifest audit.
3. T27: Joomla Form field asset boundary audit.
4. T28: optional SOAP runtime-risk audit.
5. T29: reviewer audit over T25-T28.

T25-T28 are independent and can run in parallel. T29 must run after T25-T28.
