# Task Log

Use `templates/logs/task-log.template.md` for each appended entry.

## Entry

- timestamp: 2026-07-10T08:48:31+04:00
- task: Run code quality checks with PhpStorm inspections.
- files: `composer.json`, changed PHP source files, `.webtolk/docs/reports/*.md`, `.webtolk/logs/*.md`
- tools: `mcp__phpstorm.get_php_project_config`, `mcp__phpstorm.get_composer_dependencies`, `mcp__phpstorm.get_inspections`, `mcp__phpstorm.get_file_problems`, `functions.shell_command`, `functions.apply_patch`, PHP QA tools
- status: completed
- stage: assurance
- risks: low
- outcome: PhpStorm `WARNING+` inspections identified missing `ext-soap` and `ext-simplexml` Composer metadata; `composer.json` was updated and affected files rechecked clean. CLI gates passed: PHP lint, project lint helper, PHP CS Fixer dry-run, PHPCS, PHPStan, PHPUnit, and `git diff --check`.
- next-step: commit local docblock and composer metadata changes if accepted.

## Entry

- timestamp: 2026-07-10T08:35:55+04:00
- task: Add meaningful PHP docblocks using global templates where appropriate.
- files: `lib_webtolk_otpravkapochtaru/src/**/*.php`, `plg_system_wt_otpravkapochtaru/**/*.php`, `script.php`, `.webtolk/docs/reports/*.md`, `.webtolk/logs/*.md`
- tools: `mcp__phpstorm`, `mcp__serena`, `functions.shell_command`, `functions.apply_patch`, `php -l`, `tools/qa/lint-php.ps1`, `php-cs-fixer`, `phpcs`, `phpstan`, `phpunit`
- status: completed
- stage: implementation/assurance
- risks: low
- outcome: Added documentation-only docblocks that describe what methods do and summarize non-trivial normalization, REST/SOAP, installer and Joomla field behavior. Used global PHP docblock, PlugininfoField and InstallerScript templates as style/context references where appropriate.
- next-step: commit docblock-only source changes if accepted; rebuild package only when these docblocks must be included in the release archive.

## Entry

- timestamp: 2026-07-10T08:19:46+04:00
- task: Refresh `.webtolk` status after project immersion.
- files: `.webtolk/docs/reports/stage-decision.md`, `.webtolk/docs/reports/task-record.md`, `.webtolk/docs/reports/next-skill-handoff.md`, `.webtolk/docs/reports/release-notes.md`, `.webtolk/docs/reports/migration-notes.md`, `.webtolk/docs/reports/artifact-index.md`, `.webtolk/logs/*.md`
- tools: `mcp__serena`, `mcp__phpstorm`, `mcp__context7`, `functions.shell_command`, `functions.apply_patch`
- status: completed
- stage: orchestration
- risks: low
- outcome: Loaded project-local `.webtolk`, Joomla platform knowledge from `D:/.agents`, and official Joomla manual overlay; rechecked clean git state, current package hash, and archive contents without product-code changes.
- next-step: choose `release-delivery` for live installer proof or `code-assurance` after tracking SOAP credentials are configured.

## Entry

- timestamp: 2026-04-24T16:20:00+04:00
- task: Synchronize development-flow artifacts after the completed Joomla reinstall and plugin-route verification slice.
- files: `docs/briefs/development-flow-bootstrap.md`, `docs/reports/development-scope-bootstrap.md`, `docs/reports/browser-verification-report.md`, `.webtolk/logs/task-log.md`, `.webtolk/logs/agent-log.md`, `.webtolk/logs/verification-log.md`
- tools: `functions.apply_patch`, `functions.shell_command`
- status: completed
- risks: low
- stage: intake
- outcome: Flow artifacts now reflect that the `2026-04-24` runtime slice is complete. The stale plugin-settings failure tied to removed `extension_id=257` is recorded as closed, the valid current route is `extension_id=268`, and the repository is returned to a neutral `intake` state for the next user-scoped task.
- next-step: wait for the next scoped request instead of reopening the already-validated plugin settings route.

## Entry

- timestamp: 2026-04-24T15:55:00+04:00
- task: Rebuild the package, install it on `joomla.local` through the administrator installer flow, and re-check the system plugin settings page.
- files: `phing.xml`, `.packages/WT Otpravkapochtaru_0.1.0.zip`, `D:\OSPanel\home\joomla.local\public\tmp\WT Otpravkapochtaru_0.1.0.zip`, `.webtolk/tmp/verify/installer.html`, `.webtolk/tmp/verify/install-result.html`, `.webtolk/logs/task-log.md`, `.webtolk/logs/agent-log.md`, `.webtolk/logs/verification-log.md`, `docs/reports/browser-verification-report.md`
- tools: `functions.shell_command`, `mcp__firefox_devtools__`, `mcp__chrome_devtools__`
- status: interrupted
- risks: low
- stage: verification
- outcome: The package was rebuilt with `phing`, copied to the Joomla stand tmp directory, and then installed again through the administrator installer HTTP/browser flow. The install result confirms successful package and library installation, and the live extension records remain on the refreshed ids `268/269`. Final inspection of the plugin edit page for the current id `268` was started but intentionally interrupted by the user before completion.
- next-step: after the terminal session restart, continue from authenticated administrator inspection of `/administrator/index.php?option=com_plugins&task=plugin.edit&extension_id=268` and confirm whether the settings page renders without fatal errors.

## Entry

- timestamp: 2026-04-24T13:53:45+04:00
- task: Build the package, reinstall it on `joomla.local`, and ensure no old library files remain on the stand.
- files: `.packages/WT Otpravkapochtaru_0.1.0.zip`, `D:\OSPanel\home\joomla.local\public\tmp\WT Otpravkapochtaru_0.1.0.zip`, `D:\OSPanel\home\joomla.local\public\libraries\Webtolk\Otpravkapochtaru`, `D:\OSPanel\home\joomla.local\public\plugins\system\wt_otpravkapochtaru`, `.webtolk/logs/task-log.md`, `.webtolk/logs/agent-log.md`, `.webtolk/logs/verification-log.md`
- tools: `functions.shell_command`, `mcp__chrome_devtools__`
- status: completed
- risks: low
- stage: verification
- outcome: A fresh release package was built with `phing`, the previous installed package was removed from Joomla CLI, leftover library/plugin paths were confirmed absent, and the new zip was installed cleanly. The stand now exposes fresh extension ids `267/268/269` and the installed library file set matches the project source file set exactly.
- next-step: use the refreshed stand for the next scoped runtime or browser verification task.

## Entry

- timestamp: 2026-04-24T13:43:39+04:00
- task: Revalidate the existing `.webtolk` development flow bootstrap and confirm the active project stage.
- files: `.webtolk/README.md`, `.webtolk/AGENTS.md`, `.webtolk/context/project-context.yaml`, `.webtolk/evolutions/cursor.json`, `.webtolk/logs/task-log.md`, `.webtolk/logs/agent-log.md`, `docs/briefs/development-flow-bootstrap.md`, `docs/reports/development-scope-bootstrap.md`
- tools: `functions.shell_command`, `functions.apply_patch`
- status: completed
- risks: low
- stage: intake
- outcome: The `.webtolk` flow pack is already initialized and structurally valid. Bootstrap artifacts, context, cursor and neutral intake documents are present, and the latest closed-cycle records keep the repository parked in a waiting `intake` state for the next scoped task.
- next-step: start a new scoped cycle from `intake` when the next user task is defined.

## Entry

- timestamp: 2026-04-22T18:45:00+04:00
- task: Close the `AccountinfoField` simplification cycle and rotate the project to a new neutral intake.
- files: `lib_webtolk_otpravkapochtaru/src/Fields/AccountinfoField.php`, `docs/briefs/development-flow-bootstrap.md`, `docs/reports/development-scope-bootstrap.md`, `.webtolk/logs/task-log.md`, `.webtolk/logs/agent-log.md`
- tools: `functions.apply_patch`, `functions.shell_command`
- status: completed
- risks: low
- stage: intake
- outcome: The `AccountinfoField` implementation was simplified without changing the validated status scenarios or admin layout, and the active intake artifacts are reset to a neutral waiting state for the next scoped user task.
- next-step: wait for the next user-scoped task.

## Entry

- timestamp: 2026-04-22T18:35:00+04:00
- task: Close the short `_JEXEC` security check cycle and rotate the project to a new neutral intake.
- files: `lib_webtolk_otpravkapochtaru/src/Dictionaries/CountryDictionary.php`, `docs/briefs/development-flow-bootstrap.md`, `docs/reports/development-scope-bootstrap.md`, `.webtolk/logs/task-log.md`, `.webtolk/logs/agent-log.md`
- tools: `functions.apply_patch`, `functions.shell_command`
- status: completed
- risks: low
- stage: intake
- outcome: The only missing Joomla guard in the package code was fixed in `CountryDictionary.php`, the `_JEXEC` question is closed, and the active intake artifacts are reset to a neutral waiting state for the next scoped user task.
- next-step: wait for the next user-scoped task.

## Entry

- timestamp: 2026-04-22T18:20:00+04:00
- task: Close the short analysis-only `Registry` review turn and rotate the project to a new neutral intake.
- files: `docs/briefs/development-flow-bootstrap.md`, `docs/reports/development-scope-bootstrap.md`, `.webtolk/logs/task-log.md`, `.webtolk/logs/agent-log.md`
- tools: `functions.apply_patch`, `functions.shell_command`
- status: completed
- risks: low
- stage: intake
- outcome: The completed cleanup cycle remains closed, the `Registry` question was answered without implementation work, and the active intake artifacts are reset to a neutral waiting state for the next scoped user task.
- next-step: wait for the next user-scoped task.

## Entry

- timestamp: 2026-04-22T18:05:00+04:00
- task: Remove dead donor-era methods from the public facade, refresh the read-only baseline, and close the cleanup cycle.
- files: `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php`, `lib_webtolk_otpravkapochtaru/src/Exception/UnsupportedEndpointException.php`, `.webtolk/tmp/verify/joomla-local-api-sweep.php`, `.webtolk/tmp/verify/joomla-local-unsupported-probe.php`, `docs/dumps/api-sweep-20260422-joomla-local-remediation-05/index.json`, `docs/briefs/development-flow-bootstrap.md`, `docs/reports/development-scope-bootstrap.md`, `docs/reports/implementation-plan.md`, `docs/reports/decision-log.md`, `docs/reports/release-notes.md`, `docs/reports/migration-notes.md`, `docs/reports/patch.md`, `docs/reports/evolution-report.md`, `docs/reports/donor-current-live-comparison.md`, `docs/reports/change-summary.md`, `docs/reports/changed-files.md`, `.webtolk/patches/patch-20260422-1800-public-surface-prune.md`, `.webtolk/evolutions/cursor.json`, `.webtolk/logs/task-log.md`, `.webtolk/logs/agent-log.md`, `.webtolk/logs/verification-log.md`
- tools: `functions.shell_command`, `functions.apply_patch`
- status: completed
- risks: low
- stage: evolve
- outcome: `getBalance()`, `getCategoryList()`, `getCategoryDescription()`, and `getObjectInfo()` were removed from the public facade instead of being preserved as unsupported stubs. Browser execution on `joomla.local` now records the reduced-surface baseline `16 ok / 0 error / 14 skipped`.
- stage-change: intake -> implementation -> assurance -> release -> evolve
- next-step: wait in intake for the next user-scoped task.

## Entry

- timestamp: 2026-04-22T17:45:00+04:00
- task: Close the non-tracking remediation cycle through `release` and `evolve`, then open the next intake for the remaining unsupported methods.
- files: `docs/reports/release-notes.md`, `docs/reports/migration-notes.md`, `docs/reports/patch.md`, `docs/reports/evolution-report.md`, `.webtolk/patches/patch-20260422-1730-nontracking-remediation-cycle.md`, `.webtolk/evolutions/cursor.json`, `docs/briefs/development-flow-bootstrap.md`, `docs/reports/development-scope-bootstrap.md`, `.webtolk/logs/task-log.md`, `.webtolk/logs/agent-log.md`, `.webtolk/logs/verification-log.md`
- tools: `functions.shell_command`, `functions.apply_patch`
- status: completed
- risks: medium
- stage: intake
- outcome: The remediation cycle is formally closed as a partial-compatible release backed by the browser baseline `16 ok / 0 error / 18 skipped`. The project is now reset to a new `intake` focused on `getBalance()`, `getCategoryList()`, `getCategoryDescription()`, `getObjectInfo()`, and optional mutation-path verification.
- stage-change: assurance -> release -> evolve -> new cycle (intake)
- next-step: start the next cycle only for the remaining unsupported methods and any explicitly approved mutation checks.

## Entry

- timestamp: 2026-04-22T17:18:25+04:00
- task: Refresh the assurance baseline after DB recovery and the `CountryDictionary` remediation.
- files: `lib_webtolk_otpravkapochtaru/src/Dictionaries/CountryDictionary.php`, `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php`, `.webtolk/tmp/verify/joomla-local-api-sweep.php`, `docs/dumps/api-sweep-20260422-joomla-local-remediation-05/index.json`, `docs/reports/donor-current-live-comparison.md`, `.webtolk/logs/task-log.md`, `.webtolk/logs/agent-log.md`, `.webtolk/logs/verification-log.md`
- tools: `functions.shell_command`, `functions.apply_patch`
- status: completed
- risks: medium
- stage: assurance
- outcome: The browser sweep on `joomla.local` now reproduces `16 ok / 0 error / 18 skipped`. `getCountryList()` has moved from unsupported to verified by returning official reference data through `CountryDictionary`, while the only remaining unsupported live gaps are `getBalance()`, `getCategoryList()`, `getCategoryDescription()` and `getObjectInfo()`. The cycle remains in `assurance` with a stronger verification baseline and no transport/runtime errors on the confirmed surface.
- next-step: either close the cycle as a partial compatibility release with explicit unsupported endpoints, or re-enable mutation checks for a full write-path verification run.

## Entry

- timestamp: 2026-04-22T15:12:00+04:00
- task: Close the endpoint-remediation implementation slice and move the current cycle to `assurance`.
- files: `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php`, `lib_webtolk_otpravkapochtaru/src/Exception/UnsupportedEndpointException.php`, `.webtolk/tmp/verify/joomla-local-api-sweep.php`, `docs/reports/implementation-plan.md`, `docs/reports/donor-current-live-comparison.md`, `.webtolk/logs/task-log.md`, `.webtolk/logs/agent-log.md`, `.webtolk/logs/verification-log.md`
- tools: `functions.shell_command`, `functions.apply_patch`
- status: completed
- risks: medium
- stage: assurance
- outcome: The non-tracking facade is now verification-clean for the confirmed live surface. Browser execution on `joomla.local` reproduces `15 ok / 0 error / 19 skipped`, with the remaining unresolved methods made explicit as unsupported rather than left as stale failing mappings. This is sufficient to advance the cycle from remediation implementation to `assurance`.
- stage-change: implementation -> assurance
- next-step: prepare assurance artifacts and decide whether to close the cycle as a partial compatibility release with explicit unsupported endpoints.

- timestamp: 2026-04-22T14:05:00+04:00
- task: Complete the donor/current/live investigation stage for non-tracking endpoint remediation.
- files: `docs/reports/donor-current-live-comparison.md`, `docs/reports/decision-log.md`, `docs/reports/implementation-plan.md`, `.webtolk/logs/task-log.md`
- tools: `mcp__phpstorm__`, `functions.shell_command`, `functions.apply_patch`
- status: completed
- risks: medium
- stage: investigation
- outcome: A full donor/current/live comparison matrix now classifies working mappings, donor-stale endpoints, local regressions and unverified new mappings. The remediation plan is now sequenced: fix `getShippingPoints()`, restore donor-style postoffice transport, normalize business errors, then rerun the Joomla-bootstrap sweep.

## Entry

- timestamp: 2026-04-22T13:40:00+04:00
- task: Start the remediation cycle for donor-versus-live non-tracking API mappings after the completed `joomla.local` sweep.
- files: `docs/briefs/development-flow-bootstrap.md`, `docs/reports/development-scope-bootstrap.md`, `.webtolk/logs/task-log.md`, `docs/dumps/api-sweep-20260422-joomla-local/index.json`, `docs/dumps/api-sweep-20260422-joomla-local/transport-log.ndjson`, `docs/lapay-group-russian-post-library/RussianPost-1.0.2/src/Providers/OtpravkaApi.php`
- tools: `mcp__phpstorm__`, `functions.apply_patch`, `functions.shell_command`
- status: completed
- risks: medium
- stage: intake
- next-step: produce a donor/current/live comparison matrix, fix confirmed-invalid mappings in the library, and repeat the Joomla-bootstrap non-tracking sweep on `joomla.local`.

## Entry

- timestamp: 2026-04-22T13:18:00+04:00
- task: Execute Joomla-bootstrap API sweep on `joomla.local`, persist live dumps, and verify current non-tracking library behavior against the installed stand.
- files: `.webtolk/tmp/verify/joomla-local-api-sweep.php`, `D:\OSPanel\home\joomla.local\public\tmp\wt_otpravkapochtaru_api_sweep.php`, `D:\OSPanel\home\joomla.local\public\libraries\Webtolk\Otpravkapochtaru\src\Request.php`, `docs/dumps/api-sweep-20260422-joomla-local/index.json`, `docs/dumps/api-sweep-20260422-joomla-local/transport-log.ndjson`, `docs/reports/changed-files.md`, `docs/reports/change-summary.md`
- tools: `mcp__phpstorm__`, `functions.shell_command`, `functions.apply_patch`, `tool_search`
- status: completed
- risks: medium
- stage: assurance
- outcome: Joomla bootstrap runner and stand wrapper executed successfully on `joomla.local`; all 34 non-tracking facade methods were exercised with dumps saved to `docs/dumps/api-sweep-20260422-joomla-local`; the run confirmed working order and batch flows and exposed multiple stale or invalid endpoint mappings in the current library surface.

## Entry

- timestamp: 2026-04-22T13:05:00+04:00
- task: Start a new cycle for Joomla-bootstrap library sweep, JSON response logging, and live schema dumps.
- files: `docs/briefs/development-flow-bootstrap.md`, `docs/reports/development-scope-bootstrap.md`, `.webtolk/logs/task-log.md`
- tools: `mcp__phpstorm__`, `functions.apply_patch`, `functions.shell_command`
- status: completed
- risks: medium
- stage: intake
- next-step: implement the stand bootstrap runner, add temporary JSON logging on `joomla.local`, and execute the non-tracking method sweep with dumps.

## Entry

- timestamp: 2026-04-22T12:40:00+04:00
- task: Close the current `AccountinfoField` cycle through `release` and `evolve`.
- files: `docs/reports/release-notes.md`, `docs/reports/migration-notes.md`, `docs/reports/patch.md`, `docs/reports/evolution-report.md`, `.webtolk/patches/patch-20260422-1230-accountinfo-field-cycle.md`, `.webtolk/evolutions/cursor.json`, `.webtolk/logs/task-log.md`, `.webtolk/logs/agent-log.md`, `.webtolk/logs/verification-log.md`
- tools: `functions.shell_command`, `functions.apply_patch`
- status: completed
- risks: low
- stage: evolve
- outcome: The `2026-04-22` cycle is formally closed. Release artifacts now reflect the verified library `AccountinfoField` work, a new patch id is registered, and the cursor points to the latest no-update evolution decision.
- stage-change: assurance -> release -> evolve -> new cycle (intake)
- next-step: wait in `intake` for the next scoped user task.

## Entry

- timestamp: 2026-04-22T12:30:00+04:00
- task: Revalidate `.webtolk` development flow bootstrap and record the active stage for the current cycle.
- files: `.webtolk/README.md`, `.webtolk/AGENTS.md`, `.webtolk/context/project-context.yaml`, `.webtolk/config/config.yaml`, `.webtolk/logs/task-log.md`, `.webtolk/logs/verification-log.md`, `docs/briefs/development-flow-bootstrap.md`, `docs/reports/development-scope-bootstrap.md`
- tools: `functions.shell_command`, `functions.apply_patch`
- status: completed
- risks: low
- stage: assurance
- outcome: `.webtolk` bootstrap is confirmed valid; required runtime directories, context, config and stage artifacts exist. The latest scoped cycle has intake, scope, implementation and verification evidence recorded, but it has not yet been formally closed through `assurance`, `release` and `evolve`.
- next-step: either close the current cycle from `assurance` to `evolve`, or start a new intake only after that closure is explicitly recorded.

## Entry

- timestamp: 2026-04-22T09:56:12+04:00
- task: Start a new development cycle for the library `AccountinfoField` and end-to-end admin verification.
- files: `docs/briefs/development-flow-bootstrap.md`, `docs/reports/development-scope-bootstrap.md`, `docs/Webtolk-joomla-library/src/Fields/AccountinfoField.php`, `lib_webtolk_otpravkapochtaru/src/Fields/AccountinfoField.php`, `plg_system_wt_otpravkapochtaru/config/config.xml`, `plg_system_wt_otpravkapochtaru/wt_otpravkapochtaru.xml`
- tools: `mcp__phpstorm__`, `functions.shell_command`
- status: completed
- risks: medium
- stage: intake
- next-step: implement the corrected library-based `AccountinfoField`, then rebuild, reinstall and browser-verify it on the local Joomla stand.

## Entry

- timestamp: 2026-04-21T00:00:00+04:00
- task: Initialize project development flow in `.webtolk`
- files: `.webtolk/context/project-context.yaml`, `docs/briefs/development-flow-bootstrap.md`, `docs/reports/development-scope-bootstrap.md`, `.webtolk/evolutions/cursor.json`
- tools: `mcp__phpstorm__`, `functions.shell_command`
- status: completed
- risks: low
- stage: intake
- next-step: create investigation artifacts and move flow to `investigation`

## Entry

- timestamp: 2026-04-21T00:20:00+04:00
- task: Record product intake for the new WT Russian Post Joomla library
- files: `docs/briefs/development-flow-bootstrap.md`, `docs/reports/development-scope-bootstrap.md`
- tools: `mcp__phpstorm__`, `functions.apply_patch`
- status: completed
- risks: medium
- stage: intake
- next-step: start `investigation` with old Webtolk vs LapayGroup method and architecture comparison

## Entry

- timestamp: 2026-04-21T00:30:00+04:00
- task: Record that backward compatibility with the old Webtolk library is not required
- files: `docs/briefs/development-flow-bootstrap.md`, `docs/reports/development-scope-bootstrap.md`, `.webtolk/context/project-context.yaml`
- tools: `mcp__phpstorm__`, `functions.apply_patch`
- status: completed
- risks: low
- stage: intake
- next-step: start `investigation` focused on LapayGroup method coverage and target Joomla architecture, without a legacy compatibility constraint

## Entry

- timestamp: 2026-04-21T00:40:00+04:00
- task: Record Joomla core-first constraint and enable Context7 as an additional source
- files: `.webtolk/context/project-context.yaml`, `docs/briefs/development-flow-bootstrap.md`, `docs/reports/development-scope-bootstrap.md`
- tools: `mcp__phpstorm__`, `mcp__context7__`, `functions.apply_patch`
- status: completed
- risks: low
- stage: intake
- next-step: start `investigation` using local Joomla docs, Context7, Serena and PhpStorm MCP under the Joomla core-first rule

## Entry

- timestamp: 2026-04-21T01:15:00+04:00
- task: Investigate donor API surface and Joomla-native implementation constraints
- files: `docs/reports/investigation-report.md`, `docs/reports/impact-analysis.md`
- tools: `mcp__phpstorm__`, `mcp__serena__`, `mcp__context7__`, `functions.shell_command`, `functions.apply_patch`
- status: completed
- risks: medium
- stage: investigation
- next-step: move to `architecture` and define the target library structure, service split and method mapping

## Entry

- timestamp: 2026-04-21T01:35:00+04:00
- task: Define domain and architecture for the new Joomla-native Russian Post library
- files: `docs/reports/decision-log.md`, `docs/reports/architecture.md`, `docs/reports/implementation-plan.md`
- tools: `mcp__phpstorm__`, `mcp__serena__`, `mcp__context7__`, `functions.shell_command`, `functions.apply_patch`
- status: completed
- risks: medium
- stage: architecture
- next-step: start `implementation` from the packaging skeleton, plugin boundary and transport layer

## Entry

- timestamp: 2026-04-21T02:05:00+04:00
- task: Implement package skeleton, plugin boundary and transport foundation
- files: `pkg_lib_wt_otpravkapochtaru.xml`, `script.php`, `lib_webtolk_otpravkapochtaru/`, `plg_system_wt_otpravkapochtaru/`, `docs/reports/changed-files.md`, `docs/reports/change-summary.md`
- tools: `mcp__phpstorm__`, `mcp__serena__`, `mcp__context7__`, `functions.shell_command`, `functions.apply_patch`
- status: completed
- risks: medium
- stage: implementation
- next-step: continue implementation with donor method mapping, response normalization and remaining domain services

## Entry

- timestamp: 2026-04-21T02:20:00+04:00
- task: Extend implementation with donor method mapping for calculation and post office APIs
- files: `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php`, `lib_webtolk_otpravkapochtaru/src/Service/CalculationService.php`, `lib_webtolk_otpravkapochtaru/src/Service/PostOfficeService.php`, `docs/reports/changed-files.md`, `docs/reports/change-summary.md`
- tools: `mcp__phpstorm__`, `mcp__serena__`, `functions.apply_patch`
- status: completed
- risks: medium
- stage: implementation
- next-step: continue implementation with remaining Otpravka/Tracking methods, response normalization and file-download handling

## Entry

- timestamp: 2026-04-21T03:05:00+04:00
- task: Extend implementation with batch, document and tracking operations
- files: `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php`, `lib_webtolk_otpravkapochtaru/src/Service/OtpravkaService.php`, `lib_webtolk_otpravkapochtaru/src/Service/TrackingService.php`, `lib_webtolk_otpravkapochtaru/src/Transport/HttpClient.php`, `lib_webtolk_otpravkapochtaru/src/Value/DownloadedFile.php`, `docs/reports/changed-files.md`, `docs/reports/change-summary.md`
- tools: `mcp__phpstorm__`, `mcp__serena__`, `functions.shell_command`, `functions.apply_patch`
- status: completed
- risks: medium
- stage: implementation
- next-step: continue implementation with the remaining Otpravka methods, returns flow, tracking ticket details and response normalization refinements

## Entry

- timestamp: 2026-04-21T03:35:00+04:00
- task: Reopen architecture and rebaseline the target structure around a Joomla-native facade instead of service/value layers
- files: `docs/reports/architecture.md`, `docs/reports/decision-log.md`, `docs/reports/implementation-plan.md`, `docs/reports/change-summary.md`, `.webtolk/context/project-context.yaml`
- tools: `mcp__phpstorm__`, `mcp__serena__`, `mcp__context7__`, `functions.shell_command`, `functions.apply_patch`
- status: completed
- risks: medium
- stage: architecture
- next-step: realign the current implementation from `Service/*`, `Transport/*` and `Value/*` to `Otpravkapochtaru` facade plus thin `Request` and `Tracking` helpers, then continue donor method mapping

## Entry

- timestamp: 2026-04-21T03:50:00+04:00
- task: Inspect the now-available WT CDEK library and fold its confirmed patterns into architecture
- files: `docs/reports/architecture.md`, `docs/reports/decision-log.md`, `docs/reports/implementation-plan.md`, `.webtolk/context/project-context.yaml`
- tools: `mcp__phpstorm__`, `functions.shell_command`, `functions.apply_patch`
- status: completed
- risks: low
- stage: architecture
- next-step: use WT CDEK as the confirmed source for thin `CacheTrait` and `LogTrait`, then return to implementation realignment

## Entry

- timestamp: 2026-04-21T04:05:00+04:00
- task: Compare Russian Post tracking single and batch SOAP modes against donor implementation
- files: `docs/tracking.pochta.ru-raw-html-docs/1 единичный доступ.html`, `docs/tracking.pochta.ru-raw-html-docs/2. пакетный доступ.html`, `docs/lapay-group-russian-post-library/RussianPost-1.0.2/src/Providers/Tracking.php`
- tools: `mcp__phpstorm__`, `functions.shell_command`, `functions.apply_patch`
- status: completed
- risks: low
- stage: architecture
- next-step: keep SOAP tracking separated from REST and model single/batch tracking as distinct request modes behind one tracking boundary

## Entry

- timestamp: 2026-04-21T04:15:00+04:00
- task: Simplify tracking architecture around donor-style single class and array-only public contract
- files: `docs/reports/architecture.md`, `docs/reports/decision-log.md`, `docs/reports/implementation-plan.md`, `.webtolk/context/project-context.yaml`
- tools: `functions.apply_patch`
- status: completed
- risks: low
- stage: architecture
- next-step: in implementation, keep tracking simple for external Joomla extensions: one `TrackingEntity` with donor single/pack logic and normalized array responses

## Entry

- timestamp: 2026-04-21T04:25:00+04:00
- task: Align external contract and tracking split with WT CDEK and WT AmoCRM references
 - files: `docs/reports/architecture.md`, `docs/reports/decision-log.md`, `docs/reports/implementation-plan.md`, `.webtolk/context/project-context.yaml`
 - tools: `functions.shell_command`, `functions.apply_patch`
- status: completed
- risks: low
- stage: architecture
- next-step: in implementation, use WT CDEK-style array returns, add `SoapRequest`, and let `TrackingEntity` switch `single`/`pack`

## Entry

- timestamp: 2026-04-21T12:00:00+04:00
- task: Rewrite implementation from scratch for the new facade contract
- files: `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php`, `lib_webtolk_otpravkapochtaru/src/Request.php`, `lib_webtolk_otpravkapochtaru/src/SoapRequest.php`, `lib_webtolk_otpravkapochtaru/src/Tracking.php`, `lib_webtolk_otpravkapochtaru/src/Configuration/CredentialsProvider.php`, `docs/reports/changed-files.md`, `docs/reports/change-summary.md`, `.webtolk/logs/joomla-orchestrator.md`, `.webtolk/logs/agent-log.md`, `.webtolk/logs/verification-log.md`
- tools: `functions.shell_command`, `functions.apply_patch`, `mcp__phpstorm__`, `mcp__serena__`
- status: completed
- risks: low
- stage: implementation
- next-step: complete remaining API methods if required by consumer scope and keep logs consistent with the new contract

## Entry

- timestamp: 2026-04-21T16:10:00+04:00
- task: Rewrite implementation from first principles and remove legacy service/transport/value layers
- files: `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php`, `lib_webtolk_otpravkapochtaru/src/Request.php`, `lib_webtolk_otpravkapochtaru/src/SoapRequest.php`, `lib_webtolk_otpravkapochtaru/src/Tracking.php`, `docs/reports/change-summary.md`, `docs/reports/changed-files.md`
- tools: `mcp__phpstorm__`, `mcp__serena__`, `mcp__context7__`, `functions.shell_command`, `functions.apply_patch`
- status: completed
- risks: medium
- stage: implementation
- next-step: add regression checks for SOAP/REST errors, then complete remaining donor endpoint coverage

## Entry

- timestamp: 2026-04-21T16:20:17+04:00
- task: Провести code style проверки, собрать тестовый пакет и подготовить/передать его на установку в Joomla.local
- files: `lib_webtolk_otpravkapochtaru`, `plg_system_wt_otpravkapochtaru`, `script.php`, `.webtolk/build/package.config.json`, `.webtolk/build/WT Otpravkapochtaru_0.1.0.zip`, `D:/OSPanel/home/joomla.local/public/tmp/WT Otpravkapochtaru_0.1.0.zip`
- tools: `functions.shell_command`, `D:\\.webtolk\\tools\\php-qa\\vendor\\bin\\php-cs-fixer.bat`, `D:\\.webtolk\\tools\\php-qa\\vendor\\bin\\phpstan.bat`, `D:\\.webtolk\\tools\\php-qa\\vendor\\bin\\phpunit.bat`, `C:\\Users\\musst\\.local\\bin\\phing.cmd`
- status: partially_completed
- risks: medium
- stage: verification
- next-step: поднять/проверить окружение Joomla.local (DB + админ-доступ) и завершить фактическую установку расширения через веб/CLI

## Entry

- timestamp: 2026-04-21T16:23:40+04:00
- task: Finalize local QA/build/deploy attempt for test package
- files: `lib_webtolk_otpravkapochtaru`, `plg_system_wt_otpravkapochtaru`, `script.php`, `.webtolk/build/WT Otpravkapochtaru_0.1.0.zip`, `D:\OSPanel\home\joomla.local\public\tmp\WT Otpravkapochtaru_0.1.0.zip`, `D:\OSPanel\home\joomla.local\public\configuration.php`
- tools: `functions.shell_command`, `D:\\.webtolk\\tools\\php-qa\\vendor\\bin\\php-cs-fixer.bat`, `D:\\.webtolk\\tools\\php-qa\\vendor\\bin\\phpstan.bat`, `C:\\Users\\musst\\.local\\bin\\phing.cmd`, `D:\\OSPanel\\modules\\PHP-8.3\\php.exe`
- status: partially_completed
- risks: medium
- stage: verification
- follow-up or residual risks: installation on Joomla.local is blocked by DB host `mariadb-11.8` in `configuration.php` (`getaddrinfo failed`); manual package built and copied to `tmp`, but Joomla CLI/bootstrap cannot reach DB.

## Entry

- timestamp: 2026-04-21T16:30:12+04:00
- task: Fix phing runtime by enabling ZipArchive in phing PHP binary and rebuild package
- files: `C:\\Users\\musst\\AppData\\Local\\Microsoft\\WinGet\\Packages\\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\\php.ini`, `phing.xml`, `.webtolk/build/WT Otpravkapochtaru_0.1.0.zip`, `D:\\OSPanel\\home\\joomla.local\\public\\tmp\\WT Otpravkapochtaru_0.1.0.zip`
- tools: `functions.shell_command`, `functions.apply_patch`, `C:\\Users\\musst\\.local\\bin\\phing.cmd`
- status: completed
- risks: low
- stage: verification
- next-step: продолжить установку расширения в Joomla.local после подтверждения доступности БД (`mariadb-11.8`).
## Entry

- timestamp: 2026-04-21T19:00:00+04:00
- task: Close assurance stage by generating required artifacts and recording residual environment blocker
- files: `docs/reports/review-findings.md`, `docs/reports/test-plan.md`, `docs/reports/test-cases.md`, `docs/reports/browser-verification-report.md`, `.webtolk/logs/task-log.md`, `.webtolk/logs/agent-log.md`, `.webtolk/logs/verification-log.md`, `.webtolk/logs/joomla-orchestrator.md`
- tools: `functions.shell_command`, `functions.apply_patch`
- status: completed
- risks: medium
- stage: assurance
- next-step: Move to release when Joomla.local DB bootstrap (`mariadb-11.8`) is fixed and install smoke checks are executed.

## Entry

- timestamp: 2026-04-21T19:10:00+04:00
- task: Close release stage and finish the current development cycle
- files: `docs/reports/release-notes.md`, `docs/reports/migration-notes.md`, `docs/reports/patch.md`, `.webtolk/patches/patch-20260421-1900-runtime-standup.md`, `.webtolk/evolutions/cursor.json`, `docs/reports/review-findings.md`, `docs/reports/test-plan.md`, `docs/reports/test-cases.md`, `docs/reports/browser-verification-report.md`
- tools: `functions.apply_patch`, `functions.shell_command`
- status: completed
- risks: medium
- stage: release
- next-step: Move to evolve only if reusable learning is extracted; otherwise archive with explicit no-update decision and await next task.
- timestamp: 2026-04-21T19:15:00+04:00
- task: Execute evolve phase and close current development cycle
- files: `docs/reports/evolution-report.md`, `docs/reports/patch.md`, `.webtolk/patches/patch-20260421-1900-runtime-standup.md`, `.webtolk/evolutions/cursor.json`, `.webtolk/logs/task-log.md`, `.webtolk/logs/agent-log.md`, `.webtolk/logs/verification-log.md`
- tools: `functions.shell_command`
- status: completed
- risks: low
- stage: evolve
- notes: Classified learning as task-local; no reusable layer was identified. `evolve` completed with explicit no-update decision; cursor updated for traceability.
- stage-change: release -> evolve -> new cycle (intake)

## Entry

- timestamp: 2026-04-21T19:20:00+04:00
- task: Start new development cycle
- files: `docs/briefs/development-flow-bootstrap.md`, `docs/reports/development-scope-bootstrap.md`, `.webtolk/context/project-context.yaml`
- tools: `functions.shell_command`
- status: completed
- risks: low
- stage: intake
- next-step: Run `intake-scope` with new task requirements when provided by the user.

## Entry

- timestamp: 2026-04-21T20:00:00+04:00
- task: Implement branded `plugininfo` and API-status `accountinfo` plugin fields for admin settings in `plg_system_wt_otpravkapochtaru`.
- files: `plg_system_wt_otpravkapochtaru/src/Field/PlugininfoField.php`, `plg_system_wt_otpravkapochtaru/src/Field/AccountinfoField.php`, `plg_system_wt_otpravkapochtaru/language/ru-RU/plg_system_wt_otpravkapochtaru.ini`, `plg_system_wt_otpravkapochtaru/language/en-GB/plg_system_wt_otpravkapochtaru.ini`, `docs/briefs/development-flow-bootstrap.md`, `docs/reports/development-scope-bootstrap.md`, `docs/reports/changed-files.md`, `docs/reports/change-summary.md`, `.webtolk/logs/agent-log.md`, `.webtolk/logs/joomla-orchestrator.md`, `.webtolk/logs/verification-log.md`, `D:\\/.webtolk/docs`, `C:\\Users\\musst\\.codex\\docs\\joomla-toolkit`, `mcp__context7__` (Joomla field/docs check)
- tools: `mcp__phpstorm__`, `mcp__context7__`, `functions.shell_command`, `functions.apply_patch`, `functions.shell_command`
- status: completed
- risks: low
- stage: implementation
- outcome: plugin info branding added; account info now always renders explicit API connectivity status with safe fallback states.
- next-step: close cycle and archive for next task.

## Entry

- timestamp: 2026-04-21T20:20:00+04:00
- task: Close implementation cycle for plugin settings admin fields and transition to next intake cycle.
- files: `plg_system_wt_otpravkapochtaru/src/Field/PlugininfoField.php`, `plg_system_wt_otpravkapochtaru/src/Field/AccountinfoField.php`, `plg_system_wt_otpravkapochtaru/language/ru-RU/plg_system_wt_otpravkapochtaru.ini`, `plg_system_wt_otpravkapochtaru/language/en-GB/plg_system_wt_otpravkapochtaru.ini`, `docs/reports/change-summary.md`, `docs/reports/changed-files.md`, `docs/briefs/development-flow-bootstrap.md`, `docs/reports/development-scope-bootstrap.md`
- tools: `mcp__phpstorm__`, `mcp__context7__`, `functions.shell_command`, `functions.apply_patch`
- status: completed
- stage: release
- risks: low
- outcome: Admin plugin settings now show branded Plugin info and API connection status in Account info; user confirmed Joomla.local shows expected output.
- next-step: Open new cycle at intake after archiving this result.

## Entry

- timestamp: 2026-04-21T20:25:00+04:00
- task: Initialize next development cycle shell after cycle closure.
- files: `docs/briefs/development-flow-bootstrap.md`, `docs/reports/development-scope-bootstrap.md`, `.webtolk/context/project-context.yaml`
- tools: `functions.shell_command`, `functions.apply_patch`
- status: completed
- stage: intake
- risks: low
- outcome: Cycle state rotated to new intake; awaiting next user task requirements.
- next-step: Receive and execute next scoped task via intake.

## Entry

- timestamp: 2026-04-21T20:40:00+04:00
- task: Debug `PlugininfoField::escape()` runtime fatal and move `accountinfo` field to library namespace following WT AmoCRM/CDEK pattern.
- files: `plg_system_wt_otpravkapochtaru/wt_otpravkapochtaru.xml`, `plg_system_wt_otpravkapochtaru/config/config.xml`, `plg_system_wt_otpravkapochtaru/src/Field/PlugininfoField.php`, `lib_webtolk_otpravkapochtaru/src/Fields/AccountinfoField.php`, `docs/briefs/development-flow-bootstrap.md`, `docs/reports/development-scope-bootstrap.md`, `docs/reports/changed-files.md`, `docs/reports/change-summary.md`
- tools: `mcp__phpstorm__`, `functions.shell_command`, `functions.apply_patch`
- status: completed
- stage: implementation
- risks: low
- outcome: Implemented namespace/manifest wiring for library-based `accountinfo`, added local HTML escaping helper in fields to avoid undefined method.

## Entry

- timestamp: 2026-04-21T20:55:00+04:00
  - task: Finalize current implementation slice for plugin field runtime bug and library field migration.
  - files: `plg_system_wt_otpravkapochtaru/src/Field/PlugininfoField.php`, `lib_webtolk_otpravkapochtaru/src/Fields/AccountinfoField.php`, `plg_system_wt_otpravkapochtaru/wt_otpravkapochtaru.xml`, `plg_system_wt_otpravkapochtaru/config/config.xml`, `.webtolk/tmp/verify/classcheck.php`, `.webtolk/tmp/verify/classcheck2.php`, `.webtolk/tmp/verify/classcheck3.php`
  - tools: `functions.apply_patch`
  - status: completed
  - stage: implementation
  - risks: low
  - outcome: Admin fields should now render without `::escape` fatal; `accountinfo` loaded from library namespace.
  - next-step: run Joomla.local quick smoke check and then transition to release/assurance or next cycle as required.

## Entry

- timestamp: 2026-04-21T21:10:00+04:00
- task: Add next-task constraint to avoid trivial helper/proxy methods (getBool/requireString-like) unless they add real domain value.
- files: `docs/reports/development-scope-bootstrap.md`
- tools: `functions.apply_patch`
- status: planned
- risks: low
- stage: intake
- next-step: apply in next implementation cycle before touching field logic refactors.

## Entry

- timestamp: 2026-04-21T21:22:00+04:00
- task: Close current implementation cycle after final settings cleanup and package validation; start new cycle.
- files: `plg_system_wt_otpravkapochtaru/config/config.xml`, `plg_system_wt_otpravkapochtaru/wt_otpravkapochtaru.xml`, `.packages/WT Otpravkapochtaru_0.1.0.zip`, `D:\OSPanel\home\joomla.local\public\plugins\system\wt_otpravkapochtaru\wt_otpravkapochtaru.xml`, `D:\OSPanel\home\joomla.local\public\plugins\system\wt_otpravkapochtaru\config\config.xml`
- tools: `functions.shell_command`, `functions.apply_patch`, `php-cs-fixer`, `phpstan`, `phing`, `php cli/joomla.php extension:install`
- status: completed
- risks: low
- stage: release
- outcome: removed `api_ops_list`, added `showon` gating (`auth_mode:key`, `auth_mode:login_password`), rebuilt and installed package on joomla.local.
- next-step: wait in intake for next user-defined task.

## Entry

- timestamp: 2026-04-22T08:29:38+04:00
- task: Clarify the original assignment and lock the restoration plan for the Joomla-native entity architecture.
- files: `docs/briefs/development-flow-bootstrap.md`, `docs/reports/development-scope-bootstrap.md`, `docs/reports/decision-log.md`, `docs/reports/architecture.md`, `docs/reports/implementation-plan.md`, `.webtolk/context/project-context.yaml`
- tools: `functions.shell_command`, `functions.apply_patch`
- status: completed
- risks: low
- stage: intake
- outcome: The project is explicitly returned to the approved target: facade-first Joomla library, internal `TrackingEntity` plus selected donor `Entity/*`, and array-return public methods only at the facade boundary.
- next-step: start the next implementation slice by restoring `TrackingEntity` and the selected donor entity subset in `lib_webtolk_otpravkapochtaru/src`.

## Entry

- timestamp: 2026-04-22T18:36:00+04:00
- task: Simplify the Joomla `AccountinfoField` implementation without changing its status handling or admin layout.
- files: `lib_webtolk_otpravkapochtaru/src/Fields/AccountinfoField.php`
- tools: `functions.shell_command`, `functions.apply_patch`
- status: completed
- risks: low
- stage: implementation
- outcome: The field keeps the current UI and status scenarios, but unnecessary helper methods and the language-prefix constant were removed. Straightforward value extraction and list-item rendering are now inlined in `getInput()` to match the intended simpler Joomla field style.
- next-step: close the cycle and return the project to a neutral intake state for the next scoped task.

## Entry

- timestamp: 2026-04-22T09:14:00+04:00
- task: Restore the internal entity architecture in the library without changing the public array-return facade contract.
- files: `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php`, `lib_webtolk_otpravkapochtaru/src/Request.php`, `lib_webtolk_otpravkapochtaru/src/TrackingEntity.php`, `lib_webtolk_otpravkapochtaru/src/Exception/TrackingException.php`, `lib_webtolk_otpravkapochtaru/src/Entity/AbstractEntity.php`, `lib_webtolk_otpravkapochtaru/src/Entity/Order.php`, `lib_webtolk_otpravkapochtaru/src/Entity/Recipient.php`, `lib_webtolk_otpravkapochtaru/src/Entity/ReturnShipment.php`, `lib_webtolk_otpravkapochtaru/src/Entity/Item.php`, `lib_webtolk_otpravkapochtaru/src/Entity/AddressReturn.php`, `lib_webtolk_otpravkapochtaru/src/Entity/CustomsDeclaration.php`, `lib_webtolk_otpravkapochtaru/src/Entity/CustomsDeclarationItem.php`, `lib_webtolk_otpravkapochtaru/src/Entity/EcomData.php`, `docs/reports/change-summary.md`, `docs/reports/changed-files.md`
- tools: `functions.shell_command`, `functions.apply_patch`
- status: completed
- risks: medium
- stage: implementation
- outcome: `TrackingEntity` and the selected donor `Entity/*` subset are restored. The facade now normalizes internal payloads through entities while continuing to return arrays to callers. `createBatch()` query handling was also corrected at the transport boundary.
- next-step: run syntax/build verification and close the implementation slice if package generation succeeds.

## Entry

- timestamp: 2026-04-22T10:18:45+04:00
- task: Rework the library `AccountinfoField`, rebuild the package and verify the installed admin output on `joomla.local`.
- files: `lib_webtolk_otpravkapochtaru/src/Configuration/CredentialsProvider.php`, `lib_webtolk_otpravkapochtaru/src/Fields/AccountinfoField.php`, `plg_system_wt_otpravkapochtaru/language/en-GB/plg_system_wt_otpravkapochtaru.ini`, `.webtolk/build/package.config.json`, `.webtolk/context/project-context.yaml`, `docs/reports/changed-files.md`, `docs/reports/change-summary.md`, `docs/reports/browser-verification-report.md`
- tools: `mcp__phpstorm__`, `functions.apply_patch`, `functions.shell_command`, `mcp__firefox_devtools__`
- status: completed
- risks: medium
- stage: verification
- outcome: The field now renders account data and API status from the library using current form params, the package installs successfully on `joomla.local`, and installed admin HTML confirms the expected account information and API limit block. Browser MCP itself remained unavailable and was recorded as an environment-level blocker.
- next-step: open the next intake cycle or continue with further library field work if the user scopes another task.

## Entry

- timestamp: 2026-04-22T14:39:29+04:00
- task: Continue the remediation cycle after the live non-tracking sweep and lock the current implementation state.
- files: `lib_webtolk_otpravkapochtaru/src/Request.php`, `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php`, `.webtolk/tmp/verify/joomla-local-api-sweep.php`, `docs/reports/implementation-plan.md`, `docs/reports/donor-current-live-comparison.md`, `.webtolk/logs/task-log.md`, `.webtolk/logs/agent-log.md`
- tools: `mcp__phpstorm__`, `tool_search`, `functions.shell_command`, `functions.apply_patch`
- status: in_progress
- stage: implementation
- risks: medium
- outcome: `getShippingPoints()` and the whole `postoffice` block are now treated as fixed in code and browser probes; the sweep runner is read-only by default and its scenario data was repaired after a local encoding regression. `delivery` and donor-stale `getBalance()` remain the active unresolved mappings.
- next-step: isolate `delivery` probe failures on `joomla.local`, then run a read-only verification sweep and classify the remaining errors.

## Entry

- timestamp: 2026-04-22T14:56:00+04:00
- task: Remediate the tariff calculation mapping in the entities-based facade using live browser evidence.
- files: `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php`, `.webtolk/tmp/verify/joomla-local-delivery-probe.php`, `docs/reports/implementation-plan.md`, `docs/reports/donor-current-live-comparison.md`, `.webtolk/logs/task-log.md`, `.webtolk/logs/agent-log.md`
- tools: `functions.apply_patch`, `functions.shell_command`, `mcp__phpstorm__`
- status: completed
- stage: implementation
- risks: medium
- outcome: `getTariff()` and `getTariffAndDeliveryPeriod()` now use live-confirmed `POST /1.0/tariff` on `otpravka-api.pochta.ru`. Browser probes against `joomla.local` confirmed both raw HTTP and library-method execution with the Saratov -> Magadan scenario. Remaining unresolved delivery items are now limited to category/object/country discovery.
- next-step: rerun the read-only sweep and classify only the still-unconfirmed methods plus donor-stale `getBalance()`.

## Entry

- timestamp: 2026-04-22T15:00:00+04:00
- task: Run the read-only browser sweep after tariff remediation.
- files: `.webtolk/tmp/verify/joomla-local-api-sweep.php`, `docs/dumps/api-sweep-20260422-joomla-local-remediation-05/index.json`, `.webtolk/logs/verification-log.md`
- tools: `functions.shell_command`
- status: blocked
- stage: implementation
- risks: medium
- outcome: The sweep completed technically but is invalid for product verification because the stand now throws Joomla DB-layer errors (`mysqli object is not fully initialized`) before API calls can be evaluated. The cycle remains in implementation until the local DB connectivity issue stops contaminating browser verification.
- next-step: keep browser-based point probes for endpoint remediation and postpone a canonical full sweep until `joomla.local` DB access is stable again.

## Entry

- timestamp: 2026-07-08T09:20:30+04:00
- task: Refresh project status through project-local development flow with Joomla platform knowledge loaded.
- files: `.webtolk/AGENTS.md`, `.webtolk/README.md`, `.webtolk/context/project-context.yaml`, `.webtolk/logs/task-log.md`, `.webtolk/logs/agent-log.md`, `.webtolk/logs/verification-log.md`, `docs/briefs/development-flow-bootstrap.md`, `docs/reports/development-scope-bootstrap.md`, `docs/reports/browser-verification-report.md`, `docs/reports/implementation-plan.md`, `docs/reports/donor-current-live-comparison.md`, `docs/reports/change-summary.md`, `docs/reports/release-notes.md`, `.webtolk/evolutions/cursor.json`, `D:/.agents/platforms/joomla/platform.json`, `D:/.agents/docs/joomla-toolkit/README.md`, `D:/.agents/docs/joomla-toolkit/joomla-architecture-rules.md`
- tools: `mcp__serena`, `functions.shell_command`, `functions.apply_patch`
- status: completed
- stage: orchestration
- risks: low
- outcome: Current stop point reconstructed from artifacts: project is in neutral intake after the 2026-04-24 reinstall/plugin-route verification, with the previous public-surface cleanup cycle closed and verified at `16 ok / 0 error / 14 skipped`.
- next-step: wait for the next scoped implementation, assurance, or release task; do not start code work from this status refresh alone.

## Entry

- timestamp: 2026-07-08T09:32:00+04:00
- task: Check whether the LapayGroup RussianPost reference library has releases newer than the local `1.0.2` copy.
- files: `docs/lapay-group-russian-post-library/RussianPost-1.0.2/composer.json`, `docs/lapay-group-russian-post-library/RussianPost-1.0.2/README.md`, `docs/lapay-group-russian-post-library/RussianPost-1.0.2/src/Providers/OtpravkaApi.php`, `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php`
- tools: `mcp__serena`, `functions.shell_command`, `web`
- status: completed
- stage: investigation
- risks: low
- outcome: No newer reference release was found. GitHub releases and remote tags both stop at `1.0.2`; `master` currently points to the same `fd2ca5e` commit as tag `1.0.2`.
- next-step: no reference-library import is needed; keep the current project implementation unless a future upstream release appears.

## Entry

- timestamp: 2026-07-08T09:34:12+04:00
- task: Verify delivery calculation and order creation on `joomla.local` after running address/FIO/phone normalization.
- files: `.webtolk/tmp/verify/joomla-local-delivery-order-check.php`, `D:\OSPanel\home\joomla.local\public\tmp\wt_otpravkapochtaru_delivery_order_check.php`, `docs/dumps/delivery-order-check-20260708/summary.json`, `docs/reports/browser-verification-report.md`
- tools: `mcp__serena`, `functions.shell_command`, `functions.apply_patch`, `curl.exe`
- status: completed
- stage: assurance
- risks: medium
- outcome: Runtime check passed. Normalization before tariff and before order creation passed; `getTariffAndDeliveryPeriod()` returned `max-days=6`, `total-rate=40902`, `total-vat=8998`; `createOrders()` returned `result-ids=[2315788012]`.
- next-step: leave the created test order in the test account unless explicit cleanup is requested.

## Entry

- timestamp: 2026-07-08T09:55:42+04:00
- task: Build release package `WT Otpravkapochtaru` version `1.0.0` with updated release dates.
- files: `.webtolk/build/package.config.json`, `pkg_lib_wt_otpravkapochtaru.xml`, `lib_webtolk_otpravkapochtaru/otpravkapochtaru.xml`, `plg_system_wt_otpravkapochtaru/wt_otpravkapochtaru.xml`, `script.php`, `.packages/WT Otpravkapochtaru_1.0.0.zip`
- tools: `mcp__phpstorm`, `functions.apply_patch`, `phing`, `php -l`
- status: completed
- stage: release
- risks: low
- outcome: Shared Phing packager prepared release metadata and created `.packages/WT Otpravkapochtaru_1.0.0.zip`; package manifests contain version `1.0.0` and creation date `08.07.2026`.
- next-step: install/upgrade the `1.0.0` package on `joomla.local` if runtime package verification is required.

## Entry

- timestamp: 2026-07-08T10:16:10+04:00
- task: Change release package version to `3.0.0` and rebuild.
- files: `.webtolk/build/package.config.json`, package manifests, product source metadata, `.packages/WT Otpravkapochtaru_3.0.0.zip`
- tools: `functions.apply_patch`, `phing`, `mcp__phpstorm`, `php -l`
- status: completed
- stage: release
- risks: low
- outcome: Shared Phing packager prepared release metadata for `3.0.0` and created `.packages/WT Otpravkapochtaru_3.0.0.zip`; archive manifests contain version `3.0.0` and creation date `08.07.2026`.
- next-step: install/upgrade the `3.0.0` package on `joomla.local` if runtime package verification is required.

## Entry

- timestamp: 2026-07-08T10:33:16+04:00
- task: Re-enter project through project-local development flow and reconstruct current stop point with Joomla platform knowledge loaded.
- files: `.webtolk/AGENTS.md`, `.webtolk/README.md`, `.webtolk/config/config.yaml`, `.webtolk/rules/axioms.md`, `.webtolk/rules/base.md`, `.webtolk/context/project-context.yaml`, `.webtolk/logs/task-log.md`, `.webtolk/logs/agent-log.md`, `.webtolk/logs/verification-log.md`, `docs/briefs/development-flow-bootstrap.md`, `docs/reports/development-scope-bootstrap.md`, `docs/reports/change-summary.md`, `docs/reports/browser-verification-report.md`, `docs/reports/release-notes.md`, `docs/reports/migration-notes.md`, `.webtolk/evolutions/cursor.json`, `D:/.agents/platforms/joomla/platform.json`, `D:/.agents/docs/joomla-toolkit/README.md`, `D:/.agents/docs/joomla-toolkit/joomla-architecture-rules.md`, `D:/.agents/docs/joomla-toolkit/joomla-extension-structures.md`
- tools: `mcp__serena`, `functions.shell_command`
- status: completed
- stage: orchestration
- risks: low
- outcome: Current state reconstructed without product-code changes. Latest deliverable is `.packages/WT Otpravkapochtaru_3.0.0.zip`; manifests and package config are at `3.0.0` with `08.07.2026`. Runtime delivery/order check on `joomla.local` passed earlier today. Release/migration text artifacts still primarily describe the earlier public-surface cleanup cycle and should be refreshed if the next task is formal delivery.
- next-step: wait for the next scoped task; if it is release delivery, refresh release notes and migration notes around version `3.0.0` before final handoff.

## Entry

- timestamp: 2026-07-08T10:43:16+04:00
- task: Read the tracking number from the latest delivery/order test and verify tracking methods on `joomla.local`.
- files: `.webtolk/tmp/verify/joomla-local-tracking-check.php`, `D:/OSPanel/home/joomla.local/public/tmp/wt_otpravkapochtaru_tracking_check.php`, `docs/dumps/tracking-check-20260708/summary.json`, `docs/reports/browser-verification-report.md`, `.webtolk/logs/task-log.md`, `.webtolk/logs/agent-log.md`, `.webtolk/logs/verification-log.md`
- tools: `mcp__serena`, `functions.apply_patch`, `functions.shell_command`, `curl.exe`
- status: blocked
- stage: assurance
- risks: medium
- outcome: Latest order `2315788012` / `codex-delivery-order-20260708_093328` resolves to barcode `80092123913448`. REST order lookup works by id and shop id. Web PHP has SOAP enabled, but installed plugin tracking credentials are empty, so `getOperationsByRpo()` and `getNpayInfo()` return SOAP authorization faults and `getTickets()` returns no ticket.
- next-step: fill valid Russian Post tracking SOAP credentials in the plugin settings, then rerun `/tmp/wt_otpravkapochtaru_tracking_check.php`.

## Entry

- timestamp: 2026-07-09T08:26:16+04:00
- task: Migrate project-local development-flow package from `.agents` to `.webtolk`.
- files: `.webtolk/**`, `.agents.backup-20260709-082459/**`, `.serena/memories/project_overview.md`, `.serena/memories/suggested_commands.md`, `.serena/memories/task_completion.md`, `docs/briefs/development-flow-bootstrap.md`, `docs/reports/*.md`, `phing.xml`, `.webtolk/tmp/verify/listzip.php`
- tools: `mcp__serena`, `functions.shell_command`, `functions.apply_patch`
- status: completed
- stage: process-migration
- risks: low
- outcome: New `.webtolk` package was created from `D:/.agents/new-development-flow/.webtolk`. Project-local context, logs, patches, evolution cursor, and build package config were restored from `.agents.backup-20260709-082459`. Generated old build install folders and old zip were classified as generated output and left only in `.agents`/backup.
- next-step: delete old `.agents` and/or `.agents.backup-20260709-082459` only after explicit confirmation.

## Entry

- timestamp: 2026-07-09T08:37:24+04:00
- task: Move repository-local temporary files, scratch logs, and verification dumps under `.webtolk/tmp`.
- files: `.webtolk/rules/axioms.md`, `.webtolk/build/package.config.json`, `.webtolk/tmp/dot-tmp/**`, `.webtolk/tmp/logs/**`, `.webtolk/tmp/verify/**`, `.webtolk/tmp/php-cs-fixer/.php-cs-fixer.cache`, `docs/reports/*.md`, `.webtolk/patches/*.md`, `.webtolk/logs/*.md`
- tools: `mcp__serena`, `functions.shell_command`, `functions.apply_patch`
- status: completed
- stage: repository-cleanup
- risks: low
- outcome: Root `.tmp`, `logs`, `tmp-verify`, and `.php-cs-fixer.cache` were moved into `.webtolk/tmp/*`. Axiom 12 now requires all future temporary files, scratch scripts, runtime dumps, browser profiles, and disposable logs to live only under `.webtolk/tmp/`. Existing durable flow logs remain in `.webtolk/logs`.
- next-step: keep root clean; create future ad-hoc verification files under `.webtolk/tmp/verify`.

## Entry

- timestamp: 2026-07-09T08:40:32+04:00
- task: Audit whether old `.agents` project history is available in `.webtolk`.
- files: `.agents/**`, `.agents.backup-20260709-082459/**`, `.webtolk/**`
- tools: `functions.shell_command`
- status: completed
- stage: repository-cleanup
- risks: low
- outcome: All old non-build project history files from `.agents` are present in `.webtolk` after path normalization, including context, evolution cursor, durable logs, telemetry, and patches. The only old files not present in `.webtolk` are generated build snapshots under `.agents/build/install_*` and `.agents/build/WT Otpravkapochtaru_0.1.0.zip`; they remain available in `.agents` and `.agents.backup-20260709-082459`.
- next-step: decide whether old generated build snapshots should be copied into `.webtolk/tmp/legacy-agents-build/` or intentionally left only in backup.

## Entry

- timestamp: 2026-07-09T08:41:30+04:00
- task: Copy legacy generated build snapshots from old `.agents` into `.webtolk`.
- files: `.agents/build/install_*`, `.agents/build/WT Otpravkapochtaru_0.1.0.zip`, `.webtolk/tmp/legacy-agents-build/**`
- tools: `functions.shell_command`
- status: completed
- stage: repository-cleanup
- risks: low
- outcome: Legacy generated build snapshots are now available under `.webtolk/tmp/legacy-agents-build/`. Active packaging configuration remains `.webtolk/build/package.config.json`.
- next-step: old `.agents` and `.agents.backup-20260709-082459` can be removed only after explicit confirmation.

## Entry

- timestamp: 2026-07-09T08:43:31+04:00
- task: Repeat project-history parity audit after legacy build transfer.
- files: `.agents/**`, `.webtolk/**`, `.webtolk/tmp/legacy-agents-build/**`
- tools: `functions.shell_command`
- status: completed
- stage: repository-cleanup
- risks: low
- outcome: All non-generated old `.agents` history files are present at direct `.webtolk` paths. All 49 old generated build files are present under `.webtolk/tmp/legacy-agents-build`. Key context, evolution, log, telemetry, and patch files match the old content after expected path normalization; task and verification logs additionally include new migration/audit entries.
- next-step: keep `.agents` and `.agents.backup-20260709-082459` until explicit cleanup approval.

## Entry

- timestamp: 2026-07-09T09:00:00+04:00
- task: Re-enter project through `.webtolk` flow, load Joomla platform knowledge, and reconstruct current stop point.
- files: `.webtolk/AGENTS.md`, `.webtolk/config/config.yaml`, `.webtolk/rules/axioms.md`, `.webtolk/rules/base.md`, `.webtolk/context/project-context.yaml`, `.webtolk/skills/flow-orchestrator/README.md`, `.webtolk/skills/flow-orchestrator/contract.yaml`, `D:/.agents/platforms/joomla/platform.json`, `D:/.agents/docs/joomla-toolkit/README.md`, `D:/.agents/docs/joomla-toolkit/joomla-architecture-rules.md`, `docs/reports/task-record.md`, `docs/reports/artifact-index.md`, `docs/reports/stage-decision.md`, `docs/reports/next-skill-handoff.md`
- tools: `mcp__serena`, `functions.shell_command`, `functions.apply_patch`
- status: completed
- stage: orchestration
- risks: low
- outcome: Current state reconstructed without product-code changes. `.webtolk` is the active project flow; Joomla platform knowledge was loaded from `D:/.agents`; release package state remains version `3.0.0`; tracking assurance remains blocked by empty installed SOAP tracking credentials.
- next-step: choose either formal `release-delivery` for `3.0.0` artifacts or `code-assurance` after filling tracking credentials on `joomla.local`.

## Entry

- timestamp: 2026-07-09T09:35:00+04:00
- task: Configure code style, tests and quality checks through global PHP QA tools.
- files: `.editorconfig`, `.php-cs-fixer.dist.php`, `phpcs.xml`, `phpstan.neon`, `phpunit.xml`, `composer.json`, `tools/qa/lint-php.ps1`, `tests/bootstrap.php`, `tests/Unit/Entity/OrderTest.php`, `tests/Unit/Dictionaries/CountryDictionaryTest.php`, `docs/reports/quality-tooling-setup.md`, `docs/reports/changed-files.md`, `docs/reports/change-summary.md`, `docs/reports/test-plan.md`, `docs/reports/test-cases.md`, `docs/reports/review-findings.md`, `docs/reports/artifact-index.md`
- tools: `mcp__serena`, `functions.shell_command`, `functions.apply_patch`
- status: completed
- stage: implementation-assurance
- risks: medium
- outcome: Project-local QA entrypoints now use global binaries from `D:/.agents/tools/php-qa`; PHP lint, PHPUnit and PHPStan pass; full style gates are configured and expose existing formatting debt in product source.
- next-step: run a dedicated style-cleanup task if full `php-cs-fixer` / `phpcs` gates must be green before delivery.

## Entry

- timestamp: 2026-07-09T09:20:39+04:00
- task: Apply configured QA tools as separate tasks.
- files: `lib_webtolk_otpravkapochtaru/src/**/*.php`, `plg_system_wt_otpravkapochtaru/**/*.php`, `tests/**/*.php`, `docs/reports/changed-files.md`, `docs/reports/change-summary.md`, `docs/reports/test-plan.md`, `docs/reports/test-cases.md`, `docs/reports/review-findings.md`, `docs/reports/quality-tooling-setup.md`, `docs/reports/artifact-index.md`
- tools: `functions.shell_command`, `functions.apply_patch`, `php -l`, `phpunit`, `phpstan`, `php-cs-fixer`, `phpcs`, `phpcbf`
- status: completed
- stage: implementation-assurance
- risks: low
- outcome: PHP lint, PHPUnit, PHPStan, PHP CS Fixer dry-run, and PHPCS are green after applying PHP CS Fixer to 18 files and PHPCBF to 3 files.
- next-step: review the broad formatting diff before any formal release packaging.

## Entry

- timestamp: 2026-07-09T10:15:00+04:00
- task: Move old root docs into `.webtolk` and create Russian public documentation.
- files: `docs/README.md`, `docs/developer-api.md`, `docs/joomla-user-guide.md`, `.webtolk/docs/root-docs-archive-20260709/**`, `.webtolk/docs/reports/artifact-index.md`, `.webtolk/docs/reports/changed-files.md`, `.webtolk/docs/reports/change-summary.md`, `.webtolk/docs/reports/test-plan.md`, `.webtolk/docs/reports/test-cases.md`, `.webtolk/docs/reports/review-findings.md`
- tools: `mcp__serena.search_for_pattern`, `functions.shell_command`, `functions.apply_patch`
- status: completed
- stage: documentation
- risks: low
- outcome: Previous root docs were preserved under `.webtolk/docs/root-docs-archive-20260709/`. New root `docs/` now contains Russian developer and Joomla user documentation for the current package API and plugin workflow.
- next-step: execute documentation examples only when valid API credentials are available.

## Entry

- timestamp: 2026-07-09T10:35:00+04:00
- task: Review documentation for code examples, data structures, method behavior and purpose.
- files: `docs/README.md`, `docs/developer-api.md`, `docs/facade-method-reference.md`, `.webtolk/docs/reports/changed-files.md`, `.webtolk/docs/reports/change-summary.md`, `.webtolk/docs/reports/test-cases.md`, `.webtolk/docs/reports/review-findings.md`, `.webtolk/docs/reports/artifact-index.md`
- tools: `mcp__serena.search_for_pattern`, `functions.shell_command`, `functions.apply_patch`
- status: completed
- stage: documentation-review
- risks: low
- outcome: Added practical facade reference covering all public facade methods with purpose, behavior, code examples and typical data structures. Expanded developer docs with practical examples for entity and low-level public helper classes.
- next-step: execute selected examples against a Joomla stand only when working Почта России credentials are available.

## Entry

- timestamp: 2026-07-09T11:05:00+04:00
- task: Create public WebTolk GitHub repository, README, GPL-3.0 license, commit and push.
- files: `README.md`, `LICENSE`, `.gitignore`, public source tree, tests, `docs/**`, `.webtolk/docs/reports/*.md`, `.webtolk/logs/*.md`
- tools: `functions.shell_command`, `functions.apply_patch`, `gh`, `git`, `php -l`, `phpunit`, `phpstan`, `php-cs-fixer`, `phpcs`
- status: completed
- stage: release-delivery
- risks: low
- outcome: Public repository `https://github.com/WebTolk/WT-Otpravkapochtaru-joomla-library` was created. Commit `b4b9a83` (`Initial public release`) was pushed to `main`.
- next-step: wait for GitHub license detection if the UI does not immediately show GPL-3.0 metadata; the `LICENSE` file is already present.

## Entry

- timestamp: 2026-07-09T11:45:00+04:00
- task: Apply WebTolk installer script pattern and normalize PHP docblocks.
- files: `script.php`, `language/en-GB/pkg_lib_wt_otpravkapochtaru.sys.ini`, `language/ru-RU/pkg_lib_wt_otpravkapochtaru.sys.ini`, `lib_webtolk_otpravkapochtaru/src/**/*.php`, `plg_system_wt_otpravkapochtaru/services/provider.php`, `plg_system_wt_otpravkapochtaru/src/**/*.php`
- tools: `mcp__serena.search_for_pattern`, `functions.shell_command`, `functions.apply_patch`, `php -l`, `phpunit`, `phpstan`, `php-cs-fixer`, `phpcs`, `phing`
- status: completed
- stage: release-delivery
- risks: low
- outcome: Installer script now performs Joomla/PHP preflight checks, auto-enables the system plugin on install/update, and product PHP headers use the standard WebTolk docblock layout. Release ZIP was rebuilt and inspected.
- next-step: installer/docblock changes were committed and pushed as `08b53af`.

## Entry

- timestamp: 2026-07-09T12:05:00+04:00
- task: Replace direct installer output with Joomla message queue.
- files: `script.php`, `.packages/WT Otpravkapochtaru_3.0.0.zip`
- tools: `functions.apply_patch`, `functions.shell_command`, `rg`, PHP QA tools, `phing`
- status: completed
- stage: release-delivery
- risks: low
- outcome: `postflight()` now builds the HTML string and sends it via `$this->app->enqueueMessage($html, 'info')`; direct `echo` output was removed and verified absent in source and package archive.
- next-step: commit and push the installer output fix.

## Entry

- timestamp: 2026-07-09T12:20:00+04:00
- task: Adapt branded WebTolk installer message from global `D:\.agents` source.
- files: `script.php`, `language/en-GB/pkg_lib_wt_otpravkapochtaru.sys.ini`, `language/ru-RU/pkg_lib_wt_otpravkapochtaru.sys.ini`, `.packages/WT Otpravkapochtaru_3.0.0.zip`
- tools: `mcp__serena.get_symbols_overview`, `functions.shell_command`, `functions.apply_patch`, `rg`, PHP QA tools, `phing`, archive inspection
- status: completed
- stage: release-delivery
- risks: low
- outcome: Installer now renders a WebTolk-branded HTML message and still queues it via `$this->app->enqueueMessage($html, 'info')`; rebuilt ZIP was inspected.
- next-step: committed and pushed as `c2f7b32`.

## Entry

- timestamp: 2026-07-09T12:45:00+04:00
- task: Find and apply the exact global WebTolk InstallerScript template from `D:\.agents`.
- files: `script.php`, `language/en-GB/pkg_lib_wt_otpravkapochtaru.sys.ini`, `language/ru-RU/pkg_lib_wt_otpravkapochtaru.sys.ini`, `.packages/WT Otpravkapochtaru_3.0.0.zip`
- tools: `rg`, `Get-Content`, `functions.apply_patch`, PHP QA tools, `phing`, archive inspection
- status: completed
- stage: release-delivery
- risks: low
- outcome: Exact global template structure from `D:\.agents\templates\files\InstallerScript\script.php` was adapted to this package; donor `WTMAX` identifiers were removed.
- next-step: commit and push the exact-template correction.

## Entry

- timestamp: 2026-07-09T13:05:00+04:00
- task: Normalize package name language constants.
- files: `language/en-GB/pkg_lib_wt_otpravkapochtaru.sys.ini`, `language/ru-RU/pkg_lib_wt_otpravkapochtaru.sys.ini`, `.packages/WT Otpravkapochtaru_3.0.0.zip`
- tools: `functions.apply_patch`, `phing`, `rg`, archive inspection
- status: completed
- stage: release-delivery
- risks: low
- outcome: Base package name constant is now `WT Otpravkapochtaru` in both locales and in the rebuilt ZIP.
- next-step: commit and push.

## Entry

- timestamp: 2026-07-09T13:20:00+04:00
- task: Update post-install package description and shorten settings text.
- files: `language/en-GB/pkg_lib_wt_otpravkapochtaru.sys.ini`, `language/ru-RU/pkg_lib_wt_otpravkapochtaru.sys.ini`, `.packages/WT Otpravkapochtaru_3.0.0.zip`
- tools: `functions.apply_patch`, `rg`, `phing`, archive inspection
- status: completed
- stage: release-delivery
- risks: low
- outcome: Post-install text now explains delivery calculation, shipment creation, documents, postal office lookup and tracking; settings text is shorter.
- next-step: commit and push.

## Entry

- timestamp: 2026-07-10T09:05:01+04:00
- task: Add method-level `@since` tags to newly added method docblocks.
- files: `script.php`, `lib_webtolk_otpravkapochtaru/src/**/*.php`, `plg_system_wt_otpravkapochtaru/services/provider.php`, `plg_system_wt_otpravkapochtaru/src/**/*.php`, `composer.json`, `.webtolk/docs/reports/*.md`, `.webtolk/logs/*.md`
- tools: `mcp__phpstorm.get_inspections`, `functions.shell_command`, `functions.apply_patch`, PHP QA tools
- status: completed
- stage: code-assurance
- risks: low
- outcome: Newly written method docblocks now have method-level `@since 3.0.0`, including static factory methods; pre-existing method `@since` values were preserved.
- next-step: rebuild package or commit/push only when this documentation-only slice is accepted.

## Entry

- timestamp: 2026-07-10T09:11:05+04:00
- task: Rebuild package, commit, and push method-docblock changes.
- files: `.packages/WT Otpravkapochtaru_3.0.0.zip`, tracked PHP source files, `composer.json`
- tools: `phing`, `System.IO.Compression`, `Get-FileHash`, `git`
- status: completed
- stage: release-delivery
- risks: low
- outcome: Package rebuilt and inspected; source changes committed as `d1e24d6` and pushed to `origin/main`.
- next-step: live Joomla install/update smoke test only if runtime confirmation is required.
## 2026-07-11T08:32:00+04:00 - Project immersion and status reconstruction
- Task: Load project-local development flow and mandatory Joomla knowledge, reconstruct the current stop point, and refresh required artifacts.
- Files: `.webtolk/context/project-context.yaml`, `.webtolk/docs/reports/{task-record,artifact-index,stage-decision,next-skill-handoff,release-notes,migration-notes,evolution-report}.md`, required logs.
- Tools: Serena MCP first; PowerShell shell fallback for non-code artifacts, external contracts, Git and ZIP inspection.
- Status: completed.
- Risks: tracking remains blocked by missing SOAP credentials; final package install/update smoke was not rerun live.
## 2026-07-11T08:47:09+04:00 - Detailed research-only codebase audit
- Task: Audit Joomla 5+ API compatibility, invented API, overengineering, security, and performance.
- Files: product PHP/XML/manifests, tests/configs, Joomla docs-new, Joomla 5.4.5/6.0.4/6.1.0 core, assurance reports.
- Tools: Serena MCP first; PowerShell fallback for external docs/core and read-only QA; PHP lint, PHPUnit, PHPStan, PHPCS.
- Status: completed.
- Risks: 5 medium and 5 low findings; no critical/high findings; no product code changed.
## 2026-07-11T08:55:35+04:00 - Fix audit findings 1 and 2
- Task: Replace deprecated Joomla HTTP factory and secure downloaded filenames; leave all other findings unchanged.
- Files: `lib_webtolk_otpravkapochtaru/src/Request.php`, rebuilt package, `.webtolk` reports/logs, scratch verifier.
- Tools: Serena and PhpStorm MCP preferred; shell fallback for QA, Phing, Git and ZIP inspection.
- Status: completed.
- Risks: tracked change remains uncommitted; broader test coverage is proposal-only.
## 2026-07-11T09:26:23+04:00 - Real REST shipping assurance and schema appendix
- Task: Run real shipping REST operations for Saratov to Magadan, including order edit/delete, preserve raw captures locally, anonymize responses and publish observed schemas outside the Joomla package.
- Files: `.webtolk/tmp/rest-api-capture-20260711/`, `docs/api-schemas/otpravka/`, `docs/README.md`, assurance reports/logs.
- Tools: PHPStorm MCP for file operations, inspections and integrated-terminal execution; Serena MCP for targeted preflight searches.
- Status: completed with 25 successful operations and 4 observed API/HTTP errors from 29 calls.
- Risks: document success and return edit/delete need different upstream/account preconditions; schemas are observational.
## 2026-07-11T09:58:47+04:00 - Full Russian technical documentation
- Task: Document every public method, data type and schema; provide realistic standalone examples and a README quick start.
- Files: `README.md`, `docs/README.md`, `docs/developer-api.md`, `docs/facade-method-reference.md`, `docs/api/*.md`, `docs/entities-reference.md`, `docs/low-level-api.md`.
- Tools: PHPStorm MCP for repository analysis and all file edits; Serena MCP for symbol inventory; direct shell for executable documentation assurance and Git checks.
- Status: completed; 35 facade, 29 low-level and 17 entity methods covered.
- Risks: observed schemas remain single-run evidence; no commit/push requested.
- Build: shared Phing `3. Package release` passed; package SHA-256 `6B5B7746D97FCECA754A4E237A4AE18B505AD9674F5D801FFD70615FAF30253F`.

## 2026-07-11T10:10:15+04:00 - Markdown table correction
- Task: Find and correct broken method signatures and type cells in documentation tables.
- Files: 8 public Markdown files, documentation verifier, required reports and logs.
- Tools: PHPStorm MCP first for search, edits and inspections; direct shell fallback for verifier execution after the IDE terminal used the wrong working directory.
- Status: completed; 29 union-type fragments corrected, 37 tables and 209 rows verified.
- Risks: none identified for rendering; no product-code or package change in this slice.

## 2026-07-11T10:16:24+04:00 - Documentation commit and push
- Task: Commit and push completed technical documentation and schema appendix.
- Files: `README.md`, `docs/README.md`, `docs/developer-api.md`, `docs/facade-method-reference.md`, `docs/api/*.md`, `docs/entities-reference.md`, `docs/low-level-api.md`, `docs/api-schemas/otpravka/**/*`.
- Tools: PHPStorm MCP inspection, documentation verifier, `rg` privacy scan, `git diff --cached --check`, `git commit`, `git push`.
- Status: completed; commit `3a8c9144033f5fb91562b7dce12b69150828a09a` pushed to `origin/main`.
- Risks: `lib_webtolk_otpravkapochtaru/src/Request.php` remains a separate uncommitted product-code change.

## 2026-07-11T10:27:02+04:00 - Request transport commit and package rebuild
- Task: Commit `Request.php`, rebuild the release package, and push.
- Files: `lib_webtolk_otpravkapochtaru/src/Request.php`, `.packages/WT Otpravkapochtaru_3.0.0.zip`, `.webtolk` reports and logs.
- Tools: Serena symbol overview, PHPStorm MCP inspection, PHP QA tools, filename verifier, Phing shared packager, ZIP inspection, Git.
- Status: completed; commit `ee582cd51db5b5572d0d291ed7214beed73dd021` pushed to `origin/main`.
- Risks: live Joomla install/update smoke was not rerun in this slice.

## 2026-07-11T11:19:52+04:00 - SW JProjects update metadata delivery
- Task: Create unpublished SW JProjects project on `web-tolk.ru`, add generated update/changelog URLs to package manifest, rebuild, commit and push.
- Files: `pkg_lib_wt_otpravkapochtaru.xml`, `.packages/WT Otpravkapochtaru_3.0.0.zip`, `.webtolk` reports and logs.
- Tools: Playwright MCP for Joomla administrator UI, PowerShell XML/HTTP checks, Phing shared packager, ZIP inspection, Git.
- Status: completed; project ID `119`; commit `0596f132efbf1af6e9baff0021604541fcb08024` pushed to `origin/main`.
- Risks: changelog URL returns 404 until a version/changelog record exists.

## 2026-07-11T11:50:21+04:00 - Plugin settings screenshots
- Task: Capture Russian and English Joomla administrator screenshots for `System - WT Otpravkapochtaru` plugin settings at 16:9, `1920x1080`.
- Files: `.webtolk/tmp/screenshots/plugin-settings-en-GB-1920x1080.png`, `.webtolk/tmp/screenshots/plugin-settings-ru-RU-1920x1080.png`.
- Tools: Playwright MCP against `joomla.local`; PowerShell image dimension check; visual inspection.
- Status: completed; both screenshots are viewport captures with exact `1920x1080` dimensions.
- Privacy: account fields, API counters and credential inputs were masked in the browser DOM before capture; the plugin form was not saved.

## 2026-07-11T14:45:30+04:00 - Upload plugin screenshots to WebTolk project
- Task: Upload the Russian and English plugin settings screenshots into the existing SW JProjects project on `web-tolk.ru`.
- Remote project: SW JProjects project ID `119`, element `lib_wt_otpravkapochtaru`.
- Local files: `.webtolk/tmp/screenshots/plugin-settings-ru-RU-1920x1080.png`, `.webtolk/tmp/screenshots/plugin-settings-en-GB-1920x1080.png`.
- Remote files: `https://web-tolk.ru/images/swjprojects/projects/119/ru-RU/gallery/hAGE8nogttb.png`, `https://web-tolk.ru/images/swjprojects/projects/119/en-GB/gallery/N6LXcAvFTt0.png`.
- Tools: Playwright MCP for administrator upload and save; PowerShell HEAD requests for public image availability.
- Status: completed; images uploaded into the language-specific project galleries and the project was saved.

## 2026-07-11T18:30:11+04:00 - SW JProjects publication documentation draft
- Task: Compare current documentation structure with the official Russian Post Otpravka specification, prepare Russian and English publication-ready documentation, and convert it to HTML without publishing.
- Official source: `https://otpravka.pochta.ru/specification#/main`; static route map from `https://otpravka.pochta.ru/static/js/specification.js`.
- Files: `.webtolk/tmp/swjprojects-publication-docs-20260711/`.
- Tools: web source inspection, Browser skill bootstrap attempt, PHPStorm MCP search, Serena symbol search, Pandoc conversion, PowerShell verification.
- Status: completed locally; no changes were sent to `web-tolk.ru`.
- Notes: publication text intentionally avoids large method tables and explains unsupported official API sections.

## 2026-07-11T18:37:38+04:00 - Development-flow day close
- Task: Ensure required development-flow artifacts are filled after the documentation publication draft task.
- Files: `.webtolk/docs/reports/{artifact-index,task-record,stage-decision,next-skill-handoff,change-summary,changed-files}.md`, `.webtolk/logs/{agent-log,task-log,verification-log}.md`.
- Status: completed; current handoff points to local publication drafts and notes that nothing was published remotely.
- Next action: manual review/import of HTML fragments into SW JProjects only when explicitly requested.

## 2026-07-25T18:23:33+04:00 - Development-flow re-entry status
- Task: Load project-local `.webtolk` development flow, Joomla platform knowledge, and current handoff state; report where the work stopped.
- Files: `.webtolk/AGENTS.md`, `.webtolk/config/config.yaml`, `.webtolk/rules/*.md`, `.webtolk/context/project-context.yaml`, `D:/.agents/platforms/joomla/platform.json`, `D:/.agents/docs/joomla-toolkit/*`, `D:/.agents/docs/joomla-development-articles/podklyuchenie-storonnih-php-bibliotek-v-joomla-web-tolk.md`, `.webtolk/docs/reports/*.md`, `.webtolk/logs/*.md`, `.packages/WT Otpravkapochtaru_3.0.0.zip`.
- Tools: Serena MCP and PhpStorm MCP first for project context; shell fallback for non-code artifacts, external local docs, Git and ZIP hash checks.
- Status: completed; current stop point is local-only SW JProjects publication preparation.
- Risks: no live Joomla install/update smoke or remote SW JProjects publication was performed in this status pass.

## 2026-07-25T18:32:33+04:00 - Test order creation and tracking assurance
- Task: Create a fresh Russian Post API test order on `joomla.local`, then verify tracking with plugin SOAP credentials.
- Files: `.webtolk/tmp/verify/joomla-local-create-order-and-tracking-20260725.php`, `.webtolk/tmp/order-tracking-check-20260725/*.json`, `.webtolk/docs/reports/order-tracking-runtime-assurance-20260725.md`, flow reports and logs.
- Tools: Serena symbol search first for tracking/order surface; shell fallback for ignored runtime scripts, PHP lint and execution.
- Status: completed; order `2333724273`, barcode `80214523462306`, single SOAP tracking returned one operation history record.
- Risks: batch ticket creation returned `not_create`; created test order was intentionally not deleted in this slice.

## 2026-07-25T18:39:40+04:00 - Package rebuild
- Task: Rebuild `.packages/WT Otpravkapochtaru_3.0.0.zip`.
- Files: `.webtolk/build/package.config.json`, `.packages/WT Otpravkapochtaru_3.0.0.zip`, release/process reports and logs.
- Tools: Phing shared packager via project bridge; PowerShell ZIP/hash inspection.
- Status: completed; final SHA-256 `D5AEE6C1373E5CE72CDFF531F6CA13274267FCCF0734E183CDE5B94AC193484E`.
- Risks: first rebuilt ZIP included `.playwright-mcp/` console log; fixed by adding `.playwright-mcp/` to package excludes and rebuilding.

## 2026-07-25T19:34:02+04:00 - Joomla-style PHPDoc cleanup
- Task: Read PhpStorm inspections and align method/property docblocks with Joomla style.
- Files: `script.php`, `plg_system_wt_otpravkapochtaru/src/{Extension/WtOtpravkapochtaru,Field/PlugininfoField}.php`, `lib_webtolk_otpravkapochtaru/src/{Configuration/CredentialsProvider,Dictionaries/CountryDictionary,Entity/AbstractEntity,Fields/AccountinfoField,Fields/OpslistField,Otpravkapochtaru,SoapRequest}.php`, `.packages/WT Otpravkapochtaru_3.0.0.zip`, flow reports/logs.
- Tools: Serena memory/context, PhpStorm inspections, Context7 Joomla docs, local Joomla docs, PHP QA tools, Phing shared packager, ZIP inspection.
- Status: completed; final package SHA-256 `FA4B34B34C826DDE56481D761FD280E7AE19E6C1D0D5DC43D60177CFD16EBF53`.
- Risks: Composer launcher is broken in this shell; direct QA commands passed. Remaining PhpStorm weak warnings are outside the docblock scope or conflict with project CS rules.

## 2026-07-25T19:52:54+04:00 - PHPDoc commit and push
- Task: Commit and push tracked PHPDoc cleanup source changes.
- Files: 10 tracked PHP source files from the PHPDoc cleanup.
- Tools: `git diff --check`, `git add`, `git commit`, `git push`, `git rev-parse`.
- Status: completed; commit `541a8e9d9af39f199c0274c837eb8b901fa27865` pushed to `origin/main`.
- Risks: local ignored package/process artifacts remain outside Git by repository policy.
