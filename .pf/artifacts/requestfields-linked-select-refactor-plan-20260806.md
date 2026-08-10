# Requestfields Linked Select Refactor Plan

## Metadata

- created_at: 2026-08-06T18:15:00+04:00
- run: `requestfields-linked-select-refactor-20260806`
- orchestrator: Codex
- worker_model: `gpt-5.3-codex-spark`
- worker_reasoning_effort: `high`
- status: planned

## Problem

The current linked select API uses two semantic attributes:

- `watchfield`
- `parentfield`

This solves the current `OPS -> type -> category` chain, but it is not generic enough for deeper cascades or arbitrary AJAX parameter mapping. A third-level field should not depend on hard-coded meanings such as "postoffice" and "parent type" inside the JavaScript controller.

## Target Contract

Add a generic Joomla XML field attribute:

```xml
requestfields='{"postoffice_code":"user_shipping_points","mail_type":"user_available_mail_types"}'
```

Contract:

- JSON key = AJAX request parameter name.
- JSON value = Joomla Form field name to read from the current form.
- JS attaches `change` listeners to every source field listed in the mapping.
- On any source field change, JS rebuilds the request from current values and refreshes the dependent select.
- After a dependent select is redrawn, JS dispatches bubbling `change` on that select, so deeper levels refresh naturally.

Examples:

```xml
<!-- Mail types by OPS -->
requestfields='{"postoffice_code":"user_shipping_points"}'

<!-- Mail categories by OPS and mail type -->
requestfields='{"postoffice_code":"user_shipping_points","mail_type":"user_available_mail_types"}'
```

## Compatibility Policy

Preferred new API: `requestfields`.

Temporary compatibility may remain in PHP field classes only:

- if `requestfields` is missing for mail types, derive it from legacy `watchfield` as `{"postoffice_code":"<watchfield>"}`;
- if `requestfields` is missing for mail categories, derive it from legacy `watchfield` + `parentfield` as `{"postoffice_code":"<watchfield>","mail_type":"<parentfield>"}`;
- JS should be driven by `data-wt-requestfields`; legacy `data-wt-watchfield` / `data-wt-parentfield` should not be the primary runtime contract after the refactor.

## Architecture Plan

1. Field PHP layer:
   - emit `data-wt-requestfields` with normalized JSON;
   - preserve `data-wt-url`;
   - keep field value preservation logic unchanged;
   - do not change OPS source extraction or API credential logic.

2. JavaScript layer:
   - parse `data-wt-requestfields` as a plain object;
   - resolve each mapped Joomla field using the existing resolver strategy;
   - attach one `change` listener per mapped source field;
   - build URL parameters from the mapping keys and source field values;
   - include Joomla session token as today;
   - skip/fallback safely when a required source value is empty;
   - preserve the existing disabled/loading/request-id stale-response protection;
   - dispatch `change` after successful redraw to support 3rd+ levels.

3. Tests:
   - update or add focused JS/field tests if existing test harness permits;
   - at minimum run PHP unit suite, PHP syntax checks, JS syntax check, and JSON/XML parse checks for changed files.

4. Orchestrator integration after worker:
   - review worker diff;
   - run package build;
   - install on `Joomla.local`;
   - verify current two-level chain and at least one synthetic/DOM check proving the dispatch mechanism is suitable for a third level.

## Out Of Scope For This Worker

- Do not redesign AJAX endpoint security.
- Do not change Otpravka API extraction semantics.
- Do not fix the independent WebAssetManager/JoomShopping render-order blocker unless the changed JS cannot be tested syntactically without it.
- Do not edit the external JoomShopping addon package source.

## Acceptance Criteria

- New `requestfields` attribute is documented in code/report.
- Existing `OPS -> type -> category` behavior remains expressible through `requestfields`.
- No raw HTML option injection.
- No jQuery.
- No deprecated Joomla script APIs.
- Deeper chain behavior is supported by dispatching `change` after redraw.
- Worker produces `.pf/artifacts/worker-requestfields-linked-select-report-20260806.md`.
