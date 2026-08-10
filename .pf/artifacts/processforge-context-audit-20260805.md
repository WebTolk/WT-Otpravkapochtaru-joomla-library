# ProcessForge Context Audit

## Metadata

- created_at: 2026-08-05T21:57:02+04:00
- project_id: `wt-otpravkapochtaru-joomla-library`
- audit_scope: ProcessForge project context, selected knowledge, tools, MCP, and expected specialization coverage
- product_code_changed: no

## Executive Status

Current project ProcessForge health is pass/fresh. Follow-up on 2026-08-05 selected `specialization.joomla-fullstack` for the project.

Expected context for the work:

- Joomla
- JoomShopping
- Russian Post / Otpravka API
- Joomla fullstack specialization

Actual selected project context:

- Joomla platform contract is selected.
- Joomla core/toolkit knowledge is selected.
- PHP and web knowledge appear activated in the context snapshot.
- JoomShopping knowledge packages exist in the workplace but are not selected for this project context.
- Russian Post / Otpravka API knowledge exists as project and local Joomla documentation evidence, but no standalone workplace package for the API was found.
- `specialization.joomla-fullstack` exists, passes doctor, and is selected in the refreshed project snapshot.

## ProcessForge Health Checks

Commands run:

- `project-context-check --project-root .`
- `doctor-project --project-root .`
- `specialization-doctor --workplace <workplace> --id specialization.joomla-fullstack`
- `project-override-list --project-root .`
- `project-override-doctor --project-root .`

Results:

- Project context snapshot: `fresh`
- Policy action: `continue`
- `doctor-project`: pass
- `doctor-project` warning: required capabilities `filesystem.read` and `filesystem.write` are covered by explicit runtime-access waiver
- `specialization.joomla-fullstack`: pass
- Project overrides: no entries printed; no project override is currently applied
- Project override doctor: pass; project overrides file is optional and currently not used

## Selected Platform

Selected:

- `platform.joomla`

The platform contract requires:

- `docs.joomla-6-1`
- `docs.joomla-administrator`
- `docs.joomla-core`
- `docs.joomla-toolkit`
- `php-phing-packager`
- `php-phpcs`
- `php-phpstan`
- `php-phpunit`
- template `joomla.installer-script`

Recommended by the platform contract:

- tools: `ide-phpstorm`, `php-php-cs-fixer`, `serena-cli`
- MCP: `chrome-devtools`, `phpstorm`, `playwright`, `serena`
- templates: `joomla.module-info-field`, `joomla.plugin-info-field`, `php.class-doc-block`

## Selected Knowledge Packages

Selected by project manifest / snapshot:

- `processforge.core`
- `docs.joomla-6-1`
- `docs.joomla-administrator`
- `docs.joomla-core`
- Joomla core branch/version packages
- `docs.joomla-toolkit`
- `project.wt-otpravkapochtaru-joomla-library`

Activated additionally in the context snapshot:

- `docs.php`
- `docs.web.accessibility`
- `docs.web.css`
- `docs.web.html`
- `docs.web.javascript`
- `docs.web.performance`

Not selected but available in the workplace:

- `docs.joomshopping`
- `docs.joomshopping-core`
- `docs.joomshopping-core.v5-9-1`
- `docs.joomshopping-core.v5-9-2`
- `docs.joomla-extensions.joomshopping`
- `docs.joomla-extensions.web-tolk-extensions`
- `docs.rest-api`
- `docs.web.api`

## Russian Post / Otpravka API Knowledge

No standalone workplace package matching Russian Post / Otpravka API was found in the package list.

Available evidence sources:

- Project-local package `project.wt-otpravkapochtaru-joomla-library`
- Legacy migration artifacts with saved Otpravka API docs and schemas
- Joomla 6.1 local documentation pages for `Webtolk\Otpravkapochtaru`
- WebTolk extension docs describing the JoomShopping shipping addon for Russian Post / Otpravka

Recommended correction:

- Create or register a dedicated knowledge package, for example `docs.api.otpravka-pochta-ru`.
- Make it depend on or reference the project-local Otpravka docs and the official API snapshot.
- Add it to this project context before implementation work touching tariff, post offices, mail types, mail categories, order creation, or tracking.

## Specializations

Available and valid:

- `specialization.joomla-fullstack`

Current project snapshot:

- selected specializations: `specialization.joomla-fullstack`

The fullstack specialization would add or require:

- `docs.php`
- `docs.joomla-development-articles`
- `docs.joomla-extensions`
- `docs.web.html`
- `docs.web.css`
- `docs.web.javascript`
- `php-phpcs`
- `php-phpstan`
- `php-phpunit`
- `php-phing-packager`
- `frontend-eslint`
- `frontend-stylelint`
- MCP `serena`
- recommended MCP `phpstorm`, `playwright`

Important detail:

- The specialization excludes the broad `docs.joomla` package, but it does not exclude the already selected specific Joomla packages such as `docs.joomla-core`, `docs.joomla-6-1`, and `docs.joomla-toolkit`.

Follow-up status:

- `specialization.joomla-fullstack` is now selected through `.pf/process-forge.yaml`.
- Project context snapshot was refreshed after the manifest change.

## Tools And MCP

Activated tools in snapshot:

- `ide-phpstorm`
- `php-phing-packager`
- `php-php-cs-fixer`
- `php-phpcs`
- `php-phpstan`
- `php-phpunit`
- `serena-cli`

Workplace registry also contains:

- `frontend-eslint`
- `frontend-stylelint`
- `agents-code-style`

Activated MCP in snapshot:

- `chrome-devtools`
- `phpstorm`
- `playwright`
- `serena`

Runtime observation from this audit:

- Serena MCP is available and was used for project analysis.
- `pf.py` is available at the project runtime path.
- `specialization.joomla-fullstack` doctor passes.

## Gaps Against Required Target

| Target | Status | Gap |
| --- | --- | --- |
| Joomla | OK | Selected through `platform.joomla` |
| JoomShopping | Deferred | Packages exist, but operator explicitly asked not to connect them yet |
| Russian Post / Otpravka API | Partial | Knowledge exists as project/local docs, but no dedicated selected package |
| Fullstack specialization | OK | `specialization.joomla-fullstack` exists, validates, and is selected |
| Frontend tooling | OK | `frontend-eslint` and `frontend-stylelint` are activated by the fullstack specialization |

## Recommended Next ProcessForge Change

Before code implementation, update project context requirements or project overrides so the next snapshot includes:

- `docs.api.otpravka-pochta-ru` after creating/registering that package

JoomShopping packages are intentionally not connected yet by operator instruction.

Then run:

- `project-context-refresh --project-root .`
- `project-context-check --project-root .`
- `doctor-project --project-root .`

## Implementation Readiness

Current context is ready for Joomla fullstack work. It still lacks a first-class selected Otpravka API knowledge package. JoomShopping knowledge remains intentionally deferred.
