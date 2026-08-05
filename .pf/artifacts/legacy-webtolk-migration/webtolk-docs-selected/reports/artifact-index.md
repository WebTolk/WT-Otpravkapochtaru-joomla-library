# Artifact Index

## Current Flow Artifacts
- Task record: `.webtolk/docs/reports/task-record.md`
- Stage decision: `.webtolk/docs/reports/stage-decision.md`
- Next skill handoff: `.webtolk/docs/reports/next-skill-handoff.md`
- Brief: `.webtolk/docs/briefs/development-flow-bootstrap.md`
- Scope: `.webtolk/docs/reports/development-scope-bootstrap.md`
- Investigation report: `.webtolk/docs/reports/investigation-report.md`
- Impact analysis: `.webtolk/docs/reports/impact-analysis.md`
- Decision log: `.webtolk/docs/reports/decision-log.md`
- Architecture: `.webtolk/docs/reports/architecture.md`
- Implementation plan: `.webtolk/docs/reports/implementation-plan.md`
- Changed files: `.webtolk/docs/reports/changed-files.md`
- Change summary: `.webtolk/docs/reports/change-summary.md`
- Review findings: `.webtolk/docs/reports/review-findings.md`
- Detailed codebase audit (2026-07-11): `.webtolk/docs/reports/codebase-audit-20260711.md`
- Test coverage proposal (2026-07-11): `.webtolk/docs/reports/test-coverage-proposal-20260711.md`
- Test plan: `.webtolk/docs/reports/test-plan.md`
- Test cases: `.webtolk/docs/reports/test-cases.md`
- Browser verification report: `.webtolk/docs/reports/browser-verification-report.md`
- Release notes: `.webtolk/docs/reports/release-notes.md`
- Migration notes: `.webtolk/docs/reports/migration-notes.md`
- Patch: `.webtolk/docs/reports/patch.md`
- Evolution report: `.webtolk/docs/reports/evolution-report.md`
- Developer documentation: `docs/developer-api.md`
- Practical facade method reference: `docs/facade-method-reference.md`
- Joomla user documentation: `docs/joomla-user-guide.md`
- Documentation index: `docs/README.md`
- Public repository README: `README.md`
- Public repository license: `LICENSE`
- Public repository ignore rules: `.gitignore`
- Public GitHub repository: `https://github.com/WebTolk/WT-Otpravkapochtaru-joomla-library`
- Initial public commit: `b4b9a83`
- Installer/docblock commit: `08b53af`
- Current checked HEAD: `d1e24d6`
- Archived previous root docs: `.webtolk/docs/root-docs-archive-20260709/`

## Durable Evidence
- Release package: `.packages/WT Otpravkapochtaru_3.0.0.zip`
- Current package SHA-256: `6B5B7746D97FCECA754A4E237A4AE18B505AD9674F5D801FFD70615FAF30253F`
- Package config: `.webtolk/build/package.config.json`
- Runtime delivery/order dump: `.webtolk/docs/dumps/delivery-order-check-20260708/summary.json`
- Runtime tracking dump: `.webtolk/docs/dumps/tracking-check-20260708/summary.json`
- Required logs: `.webtolk/logs/task-log.md`, `.webtolk/logs/agent-log.md`, `.webtolk/logs/verification-log.md`

## Artifact Notes
- No missing stage-template artifacts were found for the historical development cycle.
- Flow-orchestrator-specific `task-record`, `artifact-index`, `stage-decision`, and `next-skill-handoff` were created on 2026-07-09 because the contract names them explicitly.
- Release and migration notes exist, but should be refreshed if the next task is formal delivery of version `3.0.0`.
- QA tooling setup report: `.webtolk/docs/reports/quality-tooling-setup.md`.
- QA tool application evidence is recorded in `.webtolk/docs/reports/quality-tooling-setup.md`, `.webtolk/docs/reports/test-cases.md`, `.webtolk/docs/reports/review-findings.md`, `.webtolk/docs/reports/change-summary.md`, and `.webtolk/docs/reports/changed-files.md`.
- The root `docs/` folder is now reserved for public documentation; development-flow reports and historical evidence live under `.webtolk/docs/`.

## 2026-07-11 REST API Assurance Artifacts

- Detailed assurance report: `.webtolk/docs/reports/rest-api-live-shipping-assurance-20260711.md`.
- Raw sensitive captures: `.webtolk/tmp/rest-api-capture-20260711/raw/` (git-ignored, local only).
- Capture runner: `.webtolk/tmp/verify/joomla-local-shipping-api-capture.php` (git-ignored).
- Anonymization/schema generator: `.webtolk/tmp/verify/generate-public-api-schemas.php` (git-ignored).
- Schema validator: `.webtolk/tmp/verify/validate-public-api-schemas.php` (git-ignored).
- Public appendix: `docs/api-schemas/otpravka/README.md`.
- Public contract index: `docs/api-schemas/otpravka/index.json`.
- Public response examples: `docs/api-schemas/otpravka/examples/` (27 files).
- Public observed schemas: `docs/api-schemas/otpravka/schemas/` (27 files).

## 2026-07-11 Technical Documentation Artifacts

- Documentation design: `.webtolk/docs/reports/technical-documentation-design-20260711.md`.
- Documentation review: `.webtolk/docs/reports/technical-documentation-review-20260711.md`.
- Documentation verifier: `.webtolk/tmp/verify/technical-documentation-check.php`.
- Quick start: `README.md`.
- Architecture and integration guide: `docs/developer-api.md`.
- Facade method map: `docs/facade-method-reference.md`.
- Thematic API chapters: `docs/api/*.md` (7 files).
- Entity reference: `docs/entities-reference.md`.
- Low-level public API: `docs/low-level-api.md`.
- Documentation index: `docs/README.md`.
- Documentation-cycle package rebuild: `.packages/WT Otpravkapochtaru_3.0.0.zip`, SHA-256 `6B5B7746D97FCECA754A4E237A4AE18B505AD9674F5D801FFD70615FAF30253F`.

## 2026-07-11 SW JProjects Remote Project Artifacts

- Remote SW JProjects project ID: `119`.
- Project element: `lib_wt_otpravkapochtaru`.
- Manifest update commit: `0596f132efbf1af6e9baff0021604541fcb08024`.
- Update server URL: `https://web-tolk.ru/component/swjprojects/jupdate?element=lib_wt_otpravkapochtaru&debug=1`.
- Changelog URL: `https://web-tolk.ru/jchangelog?element=lib_wt_otpravkapochtaru&debug=1`.
- Uploaded Russian settings screenshot: `https://web-tolk.ru/images/swjprojects/projects/119/ru-RU/gallery/hAGE8nogttb.png`.
- Uploaded English settings screenshot: `https://web-tolk.ru/images/swjprojects/projects/119/en-GB/gallery/N6LXcAvFTt0.png`.
- Local screenshot sources: `.webtolk/tmp/screenshots/plugin-settings-ru-RU-1920x1080.png`, `.webtolk/tmp/screenshots/plugin-settings-en-GB-1920x1080.png`.
- Project remains unpublished and not visible on the frontend.

## 2026-07-11 SW JProjects Publication Documentation Drafts

- Local folder: `.webtolk/tmp/swjprojects-publication-docs-20260711/`.
- Russian Markdown: `.webtolk/tmp/swjprojects-publication-docs-20260711/publication-docs-ru.md`.
- English Markdown: `.webtolk/tmp/swjprojects-publication-docs-20260711/publication-docs-en.md`.
- Russian HTML fragment: `.webtolk/tmp/swjprojects-publication-docs-20260711/publication-docs-ru.html`.
- English HTML fragment: `.webtolk/tmp/swjprojects-publication-docs-20260711/publication-docs-en.html`.
- Official API structure comparison: `.webtolk/tmp/swjprojects-publication-docs-20260711/official-structure-comparison.md`.
- Publication payload handoff: `.webtolk/tmp/swjprojects-publication-docs-20260711/publication-payload.json`.
- Local artifact index: `.webtolk/tmp/swjprojects-publication-docs-20260711/artifact-index.md`.
- Delivery boundary: prepared locally only; no SW JProjects publication was performed.

## 2026-07-25 Re-Entry Artifact Refresh

- Current Git evidence: clean `main`, `HEAD == origin/main == 0596f132efbf1af6e9baff0021604541fcb08024`.
- Current package evidence: `.packages/WT Otpravkapochtaru_3.0.0.zip`, SHA-256 `567BC3416686FE7FC04BF8886282E82D79608FE619D136449C83AA828B40E86F`.
- Current stop-point artifacts: `.webtolk/tmp/swjprojects-publication-docs-20260711/publication-payload.json`, `publication-docs-ru.md`, `publication-docs-en.md`, `publication-docs-ru.html`, `publication-docs-en.html`, `official-structure-comparison.md`, `artifact-index.md`.
- Process files refreshed for this re-entry: `.webtolk/docs/reports/task-record.md`, `.webtolk/docs/reports/stage-decision.md`, `.webtolk/docs/reports/next-skill-handoff.md`, `.webtolk/docs/reports/artifact-index.md`, `.webtolk/docs/reports/change-summary.md`, `.webtolk/docs/reports/changed-files.md`, `.webtolk/logs/task-log.md`, `.webtolk/logs/agent-log.md`, `.webtolk/logs/verification-log.md`, `.webtolk/logs/tool-telemetry.ndjson`.

## 2026-07-25 Order Tracking Runtime Artifacts

- Assurance report: `.webtolk/docs/reports/order-tracking-runtime-assurance-20260725.md`.
- Runtime script: `.webtolk/tmp/verify/joomla-local-create-order-and-tracking-20260725.php`.
- Runtime dump root: `.webtolk/tmp/order-tracking-check-20260725/`.
- Summary dump: `.webtolk/tmp/order-tracking-check-20260725/summary.json`.
- Created order: `2333724273`, order number `codex-order-tracking-20260725_183153`, barcode `80214523462306`.

## 2026-07-25 Package Rebuild Artifacts

- Package: `.packages/WT Otpravkapochtaru_3.0.0.zip`.
- SHA-256: `D5AEE6C1373E5CE72CDFF531F6CA13274267FCCF0734E183CDE5B94AC193484E`.
- Package config adjusted: `.webtolk/build/package.config.json` excludes `.playwright-mcp/`.
- Release notes: `.webtolk/docs/reports/release-notes.md`.
- Migration notes: `.webtolk/docs/reports/migration-notes.md`.

## 2026-07-25 PHPDoc Cleanup Artifacts

- Source files: `script.php`, `lib_webtolk_otpravkapochtaru/src/{Configuration/CredentialsProvider,Dictionaries/CountryDictionary,Entity/AbstractEntity,Fields/AccountinfoField,Fields/OpslistField,Otpravkapochtaru,SoapRequest}.php`, `plg_system_wt_otpravkapochtaru/src/{Extension/WtOtpravkapochtaru,Field/PlugininfoField}.php`.
- Package: `.packages/WT Otpravkapochtaru_3.0.0.zip`.
- SHA-256: `FA4B34B34C826DDE56481D761FD280E7AE19E6C1D0D5DC43D60177CFD16EBF53`.
- Verification artifacts: `.webtolk/docs/reports/task-record.md`, `.webtolk/docs/reports/stage-decision.md`, `.webtolk/docs/reports/next-skill-handoff.md`, `.webtolk/docs/reports/change-summary.md`, `.webtolk/docs/reports/changed-files.md`, `.webtolk/logs/task-log.md`, `.webtolk/logs/agent-log.md`, `.webtolk/logs/verification-log.md`, `.webtolk/logs/tool-telemetry.ndjson`.
- Documentation sources used: Context7 Joomla Coding Standards/Manual and local `D:\Dev\Joomla-documentation\docs-new`.
