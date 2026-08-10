# Worker Task Briefs: Linked Otpravka Select Fields

Date: 2026-08-06
Run: `linked-otpravka-select-fields-20260806`
Rule: all workers are ProcessForge shell-workers, not Codex sub-agents.
Model: `gpt-5.3-codex-spark`
MCP rule: use PHPStorm MCP first; record exact evidence in the report. For the current follow-up implementation attempt, PowerShell fallback is explicitly approved by the user and orchestrator if callable PHPStorm MCP is unavailable in the worker session.

Runtime note: before editing code, each implementation worker must verify whether the PHPStorm MCP tool is actually callable in its session. The canonical local endpoint for this project is `127.0.0.1:64442`. If the worker cannot call PHPStorm MCP, it may continue with PowerShell under the local runtime waiver in `.pf/artifacts/worker-orchestration-rules-20260806.md`.

PowerShell fallback requirements for this run:

- do not edit outside the task's `allowed_files`;
- do not touch `forbidden_files`;
- avoid destructive commands;
- write only focused, reviewable changes;
- include exact PowerShell commands or command classes used in the report;
- include checks/tests run and files changed;
- expect orchestrator review before acceptance.

Context budget requirement:

- do not run broad searches over API specs, `.webtolk.backup-*`, or large documentation trees;
- do not paste large API examples into the context;
- use the prepared `.pf` source-contract artifacts as authoritative input;
- if a fact is missing, read one exact file/path only and summarize, then continue.

## Shared Architecture

Target chain:

1. OPS field changes type field.
2. Type field changes category field.

Use the old JoomShopping addon chain `OPS -> type -> category` as the correct behavior reference.

Source of truth:

- current library method `Otpravkapochtaru::getShippingPoints()`;
- selected OPS by `operator-postcode`;
- type options from selected OPS `user-available-mail-types`, with fallback to unique `user-available-products[*].mail-type`;
- category options from selected OPS `user-available-products[*].mail-category`, filtered by selected `mail-type`.

Use Joomla APIs where they fit: `Registry`, `ArrayHelper`, `HTMLHelper`, `Text`, `Session`, and Joomla JSON/response APIs.

Use JSON AJAX responses. Do not return raw HTML `<option>` strings.

## t02-library-fields-assets

Worker: `shell-worker-fields-assets`
Reasoning: high
Mode: implementation

Write scope:

- `lib_webtolk_otpravkapochtaru/src/Fields/*`
- `lib_webtolk_otpravkapochtaru/src/Service/*`
- `plg_system_wt_otpravkapochtaru/media/*`
- `plg_system_wt_otpravkapochtaru/wt_otpravkapochtaru.xml`
- `composer.json`
- `tests/Unit/Fields/*`

Forbidden:

- `plg_system_wt_otpravkapochtaru/src/Extension/*`

Implement:

1. Keep/adjust `OpslistField` only if needed for shared option formatting; do not regress current OPS behavior.
2. Add Joomla Form list fields for shipment types and shipment categories.
3. Both dependent fields must read XML attributes:
   - `watchfield`
   - `url`
   - category field also supports `parentfield` for OPS.
4. Dependent fields must render safely when no watched value exists.
5. Preserve saved values on initial render. Use either a current selected option or a full enum fallback, then let JS refresh.
6. Add a plugin-owned vanilla JS asset that:
   - finds watched fields using the CDEK-compatible strategy: `#jform_{field}`, exact `name`, and `[name$="[field]"]`;
   - sends requests to the field's relative `url`;
   - sends selected `postoffice_code` and/or `mail_type` as required;
   - includes Joomla session token when available;
   - updates `<select>` options using DOM APIs, not `innerHTML` with server HTML;
   - dispatches `change` after dependent refresh so the chain continues.
7. Register the asset in the plugin manifest/media setup according to current Joomla extension conventions.

Report:

- `.pf/artifacts/worker-fields-assets-report-20260806.md`
- include PHPStorm MCP evidence, changed files, Joomla APIs used, tests/checks run, residual risks.

Acceptance:

- no writes outside scope;
- no jQuery dependency;
- no raw HTML option injection;
- `watchfield` remains the observed-field attribute;
- `url` is mandatory for AJAX-backed dependent fields;
- saved values do not disappear before the first AJAX refresh.

## t03-plugin-ajax-endpoints

Worker: `shell-worker-plugin-ajax`
Reasoning: high
Mode: implementation

Write scope:

- `plg_system_wt_otpravkapochtaru/src/Extension/*`
- `plg_system_wt_otpravkapochtaru/src/Service/*`
- `plg_system_wt_otpravkapochtaru/language/*/*.ini`
- `tests/Unit/PluginAjax/*`

Forbidden:

- `lib_webtolk_otpravkapochtaru/src/Fields/*`
- `plg_system_wt_otpravkapochtaru/media/*`

Implement:

1. Add secure `com_ajax` JSON handlers for:
   - types by OPS;
   - categories by OPS and type.
2. Use action allow-list only.
3. Use Joomla input APIs; no direct superglobals.
4. Validate `postoffice_code` as exactly six digits.
5. Validate `mail_type` against known type enum values and selected OPS data before category filtering.
6. Build option lists from `Otpravkapochtaru::getShippingPoints()` data:
   - types: `user-available-mail-types`, with fallback to unique `mail-type`;
   - categories: unique `mail-category` for selected `mail-type`.
7. Use Joomla APIs for data traversal/normalization where suitable, especially `Registry` and `ArrayHelper`.
8. Use `Text` for labels/messages and Joomla JSON response facilities.
9. Require Joomla session token for admin-originating requests unless a runtime check proves another Joomla-native `com_ajax` mechanism is required. If a different mechanism is used, document exact evidence.
10. Never expose credentials, raw upstream API responses, stack traces, or local paths.

Response shape:

```json
{
  "success": true,
  "data": {
    "options": [
      {"value": "ORDINARY", "text": "Обыкновенное"}
    ]
  }
}
```

Report:

- `.pf/artifacts/worker-plugin-ajax-report-20260806.md`
- include PHPStorm MCP evidence, changed files, security notes, tests/checks run, residual risks.

Acceptance:

- malformed input fails closed;
- unknown action fails closed;
- missing/invalid token fails closed where token is required;
- empty API data returns safe empty options, not guessed values;
- no raw HTML output.

## t04-assurance-joomla-local

Worker: `shell-worker-assurance`
Reasoning: medium
Mode: assurance

Write scope:

- `.pf/artifacts/worker-assurance-joomla-local-20260806.md`
- `.pf/artifacts/test-report-linked-select-fields-20260806.md`

Verify:

1. PHP syntax/static checks for touched files.
2. Unit/focused tests added by `t02` and `t03`.
3. Joomla.local runtime:
   - target admin form renders OPS, type, and category fields;
   - OPS change refreshes types;
   - type change refreshes categories;
   - saved values are visible before and after refresh;
   - invalid AJAX action/input/token returns safe JSON error;
   - no secrets in UI, JSON, console, or logs inspected.

Report:

- commands run;
- Joomla.local URL/surface checked;
- screenshots or browser evidence paths if available;
- failures and exact reproduction steps.
