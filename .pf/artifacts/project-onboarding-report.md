# Project Onboarding Report

## Status

applied

## Project

- id: wt-otpravkapochtaru-joomla-library
- name: WT-Otpravkapochtaru-joomla-library
- type: joomla-extension

## Process Boundary

Project onboarding creates only the project-local `.pf/` flow root and links it to an existing workplace. It does not recreate the workplace, copy global packages into the project, or write local absolute paths to public files.

## Created First-Run Files

- .pf/START_AGENT_HERE.md
- .pf/assignments/first-assignment.yaml
- .pf/contexts/project-context.snapshot.yaml
- .pf/artifacts/project-onboarding-report.md
- .pf/reviews/project-onboarding-review.md
- .pf/handoffs/project-ready-handoff.md

## Doctor Status

fail

## Doctor Output

```text
PASS: .pf/process-forge.yaml found
PASS: public manifest has no local absolute paths
PASS: public manifest contains no secret values
PASS: project ProcessForge manifest is valid YAML
PASS: project ProcessForge manifest matches process-forge-manifest.schema.json
PASS: process-forge.local.yaml found
PASS: workplace manifest is reachable
PASS: workplace distributions registry is reachable
PASS: processforge distribution exists
PASS: processforge distribution has tools/
PASS: processforge distribution has schemas/
PASS: processforge distribution has processes/
PASS: processforge distribution has packages/
PASS: processforge distribution has templates/
PASS: .pf/hooks.yaml is valid YAML
PASS: .pf/hooks.yaml hooks.targets is a list
PASS: hooks.targets[0].id present
PASS: hooks.targets[0].type present
PASS: hooks.targets[0].enabled present
PASS: hooks.targets[0].event_types present
PASS: hooks.targets[0].event_types is a string list
PASS: hooks.targets[1].id present
PASS: hooks.targets[1].type present
PASS: hooks.targets[1].enabled present
PASS: hooks.targets[1].event_types present
PASS: hooks.targets[1].event_types is a string list
PASS: project coordination mode is inherit
PASS: effective coordination mode is simple
PASS: simple project does not require Director Office
FAIL: .gitignore missing .pf/process-forge.local.yaml

Why:
  Private local config and runtime data must stay out of public project files.

Fix:
  add .pf/process-forge.local.yaml to .gitignore
FAIL: .gitignore missing .pf/runtime/

Why:
  Private local config and runtime data must stay out of public project files.

Fix:
  add .pf/runtime/ to .gitignore
FAIL: .gitignore missing .pf/cache/

Why:
  Private local config and runtime data must stay out of public project files.

Fix:
  add .pf/cache/ to .gitignore
PASS: project package draft exists
FAIL: required capability registry declarations are missing: filesystem.read, filesystem.write

Why:
  ProcessForge capability resolution is registry-driven. This means no active provider declared the capability; it does not prove the runtime lacked actual access.

Fix:
  register the capability provider in the workplace, rerun project-onboard or project-context-refresh, then rerun doctor-project

Or:
  create .pf/artifacts/capability-waivers.yaml with explicit evidence, for example:
  schema_version: 1
  capability_waivers:
    - capability: filesystem.read
      status: active
      reason: runtime access verified; registry provider declaration is pending
      evidence: .pf/artifacts/delivery-report.md
PASS: .pf/contexts/project-context.snapshot.yaml contains no local absolute paths
PASS: .pf/contexts/project-context.snapshot.md contains no local absolute paths
PASS: knowledge resource index for project.wt-otpravkapochtaru-joomla-library optional for project-local package resources
PASS: knowledge resource index for docs.api.gitverse found
PASS: knowledge resource index for docs.api.max-platform found
PASS: knowledge resource index for docs.api.mcn-telephony found
PASS: knowledge resource index for docs.api.moo-team found
PASS: knowledge resource index for docs.device found
PASS: knowledge resource index for docs.joomla found
PASS: knowledge resource index for docs.joomla-6-1 found
PASS: knowledge resource index for docs.joomla-administrator found
PASS: knowledge resource index for docs.joomla-context7 found
PASS: knowledge resource index for docs.joomla-context7.v2026-02-21 found
PASS: knowledge resource index for docs.joomla-context7.v2026-02-21-refresh found
PASS: knowledge resource index for docs.joomla-core found
PASS: knowledge resource index for docs.joomla-core.branch-v1-5-x found
PASS: knowledge resource index for docs.joomla-core.branch-v1-x found
PASS: knowledge resource index for docs.joomla-core.branch-v2-5-x found
PASS: knowledge resource index for docs.joomla-core.branch-v3-x found
PASS: knowledge resource index for docs.joomla-core.branch-v4-x found
PASS: knowledge resource index for docs.joomla-core.branch-v5-x found
PASS: knowledge resource index for docs.joomla-core.branch-v6-x found
PASS: knowledge resource index for docs.joomla-core.v1-0-15 found
PASS: knowledge resource index for docs.joomla-core.v1-5-26 found
PASS: knowledge resource index for docs.joomla-core.v2-5-28 found
PASS: knowledge resource index for docs.joomla-core.v3-10-12 found
PASS: knowledge resource index for docs.joomla-core.v4-4-14 found
PASS: knowledge resource index for docs.joomla-core.v5-4-5 found
PASS: knowledge resource index for docs.joomla-core.v5-4-6 found
PASS: knowledge resource index for docs.joomla-core.v5-4-7 found
PASS: knowledge resource index for docs.joomla-core.v6-0-4 found
PASS: knowledge resource index for docs.joomla-core.v6-1-0 found
PASS: knowledge resource index for docs.joomla-core.v6-1-1 found
PASS: knowledge resource index for docs.joomla-core.v6-1-2 found
PASS: knowledge resource index for docs.joomla-development-articles found
PASS: knowledge resource index for docs.joomla-extensions found
PASS: knowledge resource index for docs.joomla-extensions.joomshopping found
PASS: knowledge resource index for docs.joomla-extensions.radical-form found
PASS: knowledge resource index for docs.joomla-extensions.revars found
PASS: knowledge resource index for docs.joomla-extensions.web-tolk-dev-catalog found
PASS: knowledge resource index for docs.joomla-extensions.web-tolk-extensions found
PASS: knowledge resource index for docs.joomla-toolkit found
PASS: knowledge resource index for docs.joomshopping found
PASS: knowledge resource index for docs.joomshopping-core found
PASS: knowledge resource index for docs.joomshopping-core.v5-9-1 found
PASS: knowledge resource index for docs.joomshopping-core.v5-9-2 found
PASS: knowledge resource index for docs.marketing-seo-geo-ai found
PASS: knowledge resource index for docs.php found
PASS: knowledge resource index for docs.radicalmart found
PASS: knowledge resource index for docs.radicalmart-core found
PASS: knowledge resource index for docs.radicalmart-core.radicalmart-express-v3-x.v3-0-4 found
PASS: knowledge resource index for docs.radicalmart-core.radicalmart-v2-x.v2-2-4 found
PASS: knowledge resource index for docs.radicalmart-core.radicalmart-v3-x.v3-0-23 found
PASS: knowledge resource index for docs.radicalmart-extensions found
PASS: knowledge resource index for docs.radicalmart-extensions.analytics.v1-1-0 found
PASS: knowledge resource index for docs.radicalmart-extensions.export-core.v0-2-1-dev found
PASS: knowledge resource index for docs.radicalmart-extensions.export-plugins-plg-radicalmart-export-yml.v1-0-0 found
PASS: knowledge resource index for docs.radicalmart-extensions.export-plugins-plg-radicalmart-export-yml.v1-1-1-dev found
PASS: knowledge resource index for docs.radicalmart-extensions.import-core.v0-8-6 found
PASS: knowledge resource index for docs.radicalmart-extensions.import-core.v0-9-1-dev found
PASS: knowledge resource index for docs.radicalmart-extensions.import-plugins-plg-radicalmart-import-excel.v1-0-0 found
PASS: knowledge resource index for docs.radicalmart-extensions.modules-filter-extended-mod-msg-rmf.v2-0-0 found
PASS: knowledge resource index for docs.radicalmart-extensions.plugins-crm-retailcrm.v1-0-2 found
PASS: knowledge resource index for docs.radicalmart-extensions.plugins-fields-plg-radicalmart-fields-gallery.v1-0-0 found
PASS: knowledge resource index for docs.radicalmart-extensions.plugins-fields-plg-radicalmart-fields-standard.v2-1-5 found
PASS: knowledge resource index for docs.radicalmart-extensions.plugins-media-plg-radicalmart-media-resize.v1-1-0 found
PASS: knowledge resource index for docs.radicalmart-extensions.plugins-media-plg-radicalmart-media-video.v1-0-0 found
PASS: knowledge resource index for docs.radicalmart-extensions.plugins-message-plg-radicalmart-message-email.v2-2-0 found
PASS: knowledge resource index for docs.radicalmart-extensions.plugins-payment-plg-radicalmart-payment-robokassa.v2-0-0 found
PASS: knowledge resource index for docs.radicalmart-extensions.plugins-payment-plg-radicalmart-payment-yookassa.v2-1-0 found
PASS: knowledge resource index for docs.radicalmart-extensions.plugins-shipping-plg-radicalmart-shipping-addresses.v1-0-0 found
PASS: knowledge resource index for docs.radicalmart-extensions.plugins-shipping-plg-radicalmart-shipping-apiship.v1-0-2 found
PASS: knowledge resource index for docs.radicalmart-extensions.plugins-shipping-plg-radicalmart-shipping-standard.v3-0-0 found
PASS: knowledge resource index for docs.radicalmart-extensions.plugins-shipping-plg-radicalmart-shipping-zone.v1-0-0 found
PASS: knowledge resource index for docs.radicalmart-extensions.templates-uikit-pkg-radicalmart-uikit.v3-0-16 found
PASS: knowledge resource index for docs.rest-api found
PASS: knowledge resource index for docs.seo found
PASS: knowledge resource index for docs.virtuemart found
PASS: knowledge resource index for docs.virtuemart-core found
PASS: knowledge resource index for docs.virtuemart-core.v4-4-10 found
PASS: knowledge resource index for docs.web found
PASS: knowledge resource index for docs.web.api found
PASS: knowledge resource index for docs.web.css found
PASS: knowledge resource index for docs.web.css-framework found
PASS: knowledge resource index for docs.web.html found
PASS: knowledge resource index for docs.web.javascript found
PASS: knowledge resource index for docs.web.javascript-framework found
PASS: knowledge resource index for docs.web.yandex-maps-js-api found
PASS: .pf/START_AGENT_HERE.md found
PASS: .pf/runtime/bin/pf.py found
PASS: .pf/assignments/first-assignment.yaml found
PASS: .pf/artifacts/project-profile.md found
PASS: .pf/artifacts/project-classification-report.md found
PASS: .pf/artifacts/repository-map.md found
PASS: .pf/artifacts/project-conventions.md found
PASS: .pf/artifacts/global-resource-matching-report.md found
PASS: .pf/artifacts/project-init-proposal.md found
PASS: .pf/artifacts/project-onboarding-report.md found
PASS: .pf/reviews/project-init-review.md found
PASS: .pf/reviews/project-onboarding-review.md found
PASS: .pf/handoffs/project-ready-handoff.md found
```

## Fix Hints

- Resolve the FAIL lines above, then rerun `pf doctor-project --project-root .`.
