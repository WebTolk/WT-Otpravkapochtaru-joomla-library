# Run Plan: lapaygroup-joomla-core-swap-20260811

## Goal

Validate `lapaygroup/russianpost` 2.0.0 directly inside the `joomla.local`
test stand by registering the locally supplied SDK source in Joomla's core
vendor/classloader area and running read-only integration checks with existing
plugin parameters.

Product repository code must remain unchanged.

## Worker Model

All shell-workers use `gpt-5.3-codex-spark`.

## Task Order

1. T13: snapshot current stand library/autoload state and produce a precise
   reversible swap plan.
2. T14: the only writer task; copy/register the local SDK in the Joomla stand
   core/vendor area with backup and restore instructions.
3. T15: run read-only SDK/transport/API smoke checks using plugin params.
4. T16: verify the JoomShopping/admin form surface after the core/vendor swap.
5. T17: review evidence and classify migration readiness.

## Constraints

- No product-code edits.
- No release package edits.
- No destructive stand changes without backup.
- No raw secrets in public artifacts.
- No order create/edit/delete calls.
