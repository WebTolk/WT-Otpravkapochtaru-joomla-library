# Linked Otpravka Select Fields Orchestration Plan

## Metadata

- created_at: 2026-08-06T08:40:00+04:00
- run: `linked-otpravka-select-fields-20260806`
- process: `software-feature-development`
- mode: planning
- product_code_changed: no

## Goal

Implement three linked Joomla Form list fields for WT Otpravkapochtaru:

- OPS list;
- shipment category list;
- shipment type list.

Target dependency chain:

1. OPS selection updates available shipment types.
2. Shipment type selection updates available shipment categories.

Clarification on 2026-08-06: the old JoomShopping addon chain `OPS -> type -> category` is accepted as the correct target order. The earlier `OPS -> category -> type` assumption is withdrawn.

## Sources Loaded

- `.pf/AGENTS.md`
- `.pf/process-forge.yaml`
- `.pf/contexts/project-context.snapshot.md`
- `.pf/artifacts/joomshopping-shipping-price-form-investigation.md`
- `.pf/artifacts/otpravka-api-knowledge-package-connection-20260806.md`
- `.pf/artifacts/worker-orchestration-rules-20260806.md`
- Local Joomla platform contract and toolkit under `D:/.agents/docs/joomla/core/joomla-toolkit/`
- Local PF package `docs.api.otpravka-pochta`
- Donor WT CDEK fields under `D:/Dev/WT-CDEK-Joomla-PHP-library/lib_webtolk_wtcdek/src/Fields/`
- Legacy JoomShopping addon snapshot under `.pf/tmp/pkg_smwtotpravkapochtaru_1.3.6/`

Context7 is not part of the decision basis for this plan; local sources are preferred.

## Architecture Direction

- Keep `watchfield` as the XML attribute name for the observed Joomla Form field.
- Add/use a `url` XML attribute containing a relative com_ajax URL.
- Use namespaced Joomla 5/6 FormField classes under `Webtolk\Otpravkapochtaru\Fields`.
- Keep option values as Otpravka API enum/postcode values, not translated labels.
- Use Joomla APIs for data handling where they fit: `Registry`, `ArrayHelper`, `HTMLHelper`, `Text`, `Session`, and Joomla JSON/response helpers. Avoid ad hoc traversal or manual escaping when Joomla provides a suitable API.
- Use plugin-owned media for the linked-select JS controller because the AJAX endpoint belongs to the system plugin.
- Use vanilla JS and Joomla/WebAssetManager, not jQuery.
- Return JSON option data from AJAX endpoints; do not return raw `<option>` HTML.
- Preserve saved values during initial render by allowing each dependent field to render a current/static fallback option before JS refresh.

## Proposed Field Contract

Example XML shape:

```xml
<field
    name="user_shipping_points"
    type="Opslist"
    addfieldprefix="Webtolk\Otpravkapochtaru\Fields"
    label="..."
/>

<field
    name="user_available_mail_types"
    type="Mailtypes"
    addfieldprefix="Webtolk\Otpravkapochtaru\Fields"
    watchfield="user_shipping_points"
    url="index.php?option=com_ajax&amp;plugin=wt_otpravkapochtaru&amp;group=system&amp;format=json&amp;action=getMailTypes"
    label="..."
/>

<field
    name="user_available_mail_category"
    type="Mailcategories"
    addfieldprefix="Webtolk\Otpravkapochtaru\Fields"
    watchfield="user_available_mail_types"
    parentfield="user_shipping_points"
    url="index.php?option=com_ajax&amp;plugin=wt_otpravkapochtaru&amp;group=system&amp;format=json&amp;action=getMailCategories"
    label="..."
/>
```

`watchfield` stays the primary observed field. `parentfield` is required for the category field because category filtering needs both selected OPS and selected mail type.

## AJAX Security Requirements

- Use Joomla input APIs only; no direct superglobals.
- Validate `action` against a fixed allow-list.
- Validate `postoffice_code` as a strict postal-code-like value before use.
- Validate `mail_category` and `mail_type` against known values derived from API/user settings.
- Reject unsupported methods or malformed input with JSON errors and safe status codes.
- Require a Joomla session token for administrator-originating requests unless ProcessForge/Joomla runtime investigation proves com_ajax token handling requires a different Joomla-native mechanism.
- Check that the plugin is enabled and credentials are configured.
- Do not expose access token, user key, user login/password, tracking credentials, raw API exception traces, or full upstream response bodies.
- Log technical errors only if the project logging policy allows it; do not log request secrets.
- Response shape must be stable:

```json
{
  "success": true,
  "data": {
    "options": [
      {"value": "ORDINARY", "text": "..."}
    ]
  }
}
```

## Worker Tasks

| Task | Worker | Mode | Reasoning | Write Scope | Depends On |
| --- | --- | --- | --- | --- | --- |
| `t01-api-security-design` | `shell-worker-api-security` | planning_only | high | `.pf/artifacts/worker-api-security-design-20260806.md` | none |
| `t01c-category-type-source-contract` | `shell-worker-source-contract` | planning_only | high | `.pf/artifacts/category-type-source-contract-20260806.md` | `t01b` review |
| `t02-library-fields-assets` | `shell-worker-fields-assets` | implementation | high | library field/service files, plugin media, plugin manifest, field tests | accepted `t01c` |
| `t03-plugin-ajax-endpoints` | `shell-worker-plugin-ajax` | implementation | high | plugin extension/service files, plugin language files, AJAX tests | accepted `t01c` |
| `t04-assurance-joomla-local` | `shell-worker-assurance` | assurance | medium | `.pf` assurance/test reports | `t02`, `t03`, orchestrator integration review |

No worker may edit outside its ProcessForge assignment scope. Workers must use MCP PHPStorm first and record evidence or approved fallback.

## Runtime Verification

The assurance worker must use the local Joomla stand:

- stand: `joomla.local`;
- admin credentials are environment defaults from the Joomla platform contract;
- verify admin form rendering where the target fields are used;
- verify OPS change refreshes type options;
- verify type change refreshes category options;
- verify invalid AJAX input and missing/invalid token behavior;
- verify no secret values appear in UI, JSON, logs, or browser console.

## Orchestrator Control Points

1. Start and collect `t01`.
2. Review `t01` for exact option source, endpoint shape, security gates, and unresolved API gaps.
3. Start `t02` and `t03` only after `t01` is accepted.
4. Review changed files from `t02` and `t03` for scope, compatibility, and security.
5. Run local CLI checks before `t04`.
6. Start and collect `t04`.
7. Decide release/package delivery after assurance evidence.

## Acceptance Criteria

- Three fields render in Joomla Form context with stable saved values.
- `watchfield` works as the observed-field attribute.
- Dependent selects update without jQuery and without unsafe HTML injection.
- AJAX endpoints are strict, token-protected where applicable, and return JSON only.
- CLI QA passes or residual issues are documented with exact blockers.
- `joomla.local` runtime evidence proves the linked behavior and security failure cases.
- `.pf` run, task, review, test, and handoff artifacts are current.
