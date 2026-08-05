# Next Skill Handoff

## Recommended Next Skill
- Formal delivery or installer smoke: `release-delivery`.
- Tracking validation after credentials are supplied: `code-assurance`.
- Product behavior change: `investigation`, then `domain-surface` and `architecture-plan` before implementation.

## Ready State
- Project context loaded: yes.
- Joomla platform contract and required local knowledge loaded: yes.
- Required orchestration artifacts refreshed: yes.
- Product code changed in this status pass: no.
- Git state rechecked on 2026-07-11: clean `main`, local and remote at `d1e24d6`.
- Package rechecked on 2026-07-11: `.packages/WT Otpravkapochtaru_3.0.0.zip`, SHA-256 `B20137F93DBCDBA12F27D063FA569E8753537DF8FAE219BAB66AB1350F56C9E2`.

## Handoff Summary
- Method-level docblocks and explicit Composer requirements for `ext-soap` and `ext-simplexml` are committed and pushed in `d1e24d6`.
- The rebuilt `3.0.0` ZIP contains 48 entries and aligned package/library/plugin manifest versions.
- Latest passed runtime scenario is delivery calculation plus order creation on `joomla.local` from 2026-07-08.
- Latest blocked runtime scenario is tracking SOAP because installed tracking credentials are empty.
- Project-local flow truth is `.webtolk`; pre-migration `.agents` evidence is historical only.

## Required Before Formal Release
- Rerun live Joomla install/update smoke for the final `d1e24d6` package if runtime installer proof is required.
- Preserve the verification boundary: source QA and archive checks are current; the last live delivery/order proof predates the documentation-only final commit.

## Required Before Tracking Recheck
- Fill valid Russian Post tracking SOAP credentials in the installed plugin params on `joomla.local`.
- Rerun `.webtolk/tmp/verify/joomla-local-tracking-check.php` through the Joomla test instance.

## 2026-07-11 Audit Handoff
- Full findings: `.webtolk/docs/reports/codebase-audit-20260711.md`.
- Recommended next skill for remediation: `investigation`, then `architecture-plan`, then `implementation`.
- Priority: HTTP factory migration, filename security, removal of form-render network I/O, then transport/security tests.
- Code state remains unchanged by the audit.

## 2026-07-11 Scoped Remediation Handoff

- Local tracked change: `lib_webtolk_otpravkapochtaru/src/Request.php` only.
- Package: `.packages/WT Otpravkapochtaru_3.0.0.zip`, SHA-256 `C68F71A6F4D6B7E207CDF8684C11BFA457D7A1934C2D90BACC5DD1A767E22C1A`.
- Verification: PhpStorm, lint, PHPUnit, PHPStan, PHPCS, PHP CS Fixer, filename verifier, Joomla 5/6 core check, and ZIP inspection passed.
- Next skill only if delivery is requested: `release-delivery` for commit/push and optional live Joomla install/update smoke.

## 2026-07-11 REST Assurance Handoff

- Real REST assurance used 29 requests; never call `getApiLimit()` merely to confirm the balance.
- Public appendix is ready under `docs/api-schemas/otpravka/`; raw captures stay only in `.webtolk/tmp/rest-api-capture-20260711/`.
- Order create/edit/find/batch/return-to-new/delete passed and cleanup is complete.
- Document calls returned HTTP 400; separate/direct return creation returned `FREE_ER_ADDRESS_NOT_ENABLED` and `DIRECT_SHIPMENT_NOT_FOUND` respectively.
- To test return edit/delete later, use an account with separate-return service enabled and an eligible direct shipment; do not rerun the full 29-call suite.
- Next skill for publication: `release-delivery` after reviewing the generated public appendix and current mixed working tree.

## 2026-07-11 Technical Documentation Handoff

- Full public documentation is ready locally and uncommitted.
- Review evidence: 35 facade methods, 29 low-level methods, 17 entity methods, 140 links and 60 syntax-valid PHP examples.
- Documentation corrected several stale response shapes and the old `Recipient` input example.
- Public response examples were regenerated locally after stronger FIO/GUID anonymization; no REST quota was used.
- Next skill: `release-delivery` only if commit/push is requested.
- Final package was rebuilt with shared Phing target `3. Package release`; SHA-256 `6B5B7746D97FCECA754A4E237A4AE18B505AD9674F5D801FFD70615FAF30253F`.

## 2026-07-11 Markdown Correction Handoff

- Union-type pipes in all Markdown table cells are escaped.
- The documentation verifier now checks 37 tables and 209 rows for stable structure and forbidden unescaped inline-code pipes.
- Full verification passes with 0 errors; product code and package were not touched by this correction.

## 2026-07-11 Documentation Delivery Handoff

- Documentation and API schema appendix are committed and pushed as `3a8c9144033f5fb91562b7dce12b69150828a09a`.
- `HEAD` and `origin/main` match after push.
- Verification before commit: PHPStorm `ERROR` inspection on the table-heavy facade reference, documentation verifier, privacy scan and `git diff --cached --check`.
- Working tree is intentionally not clean because `lib_webtolk_otpravkapochtaru/src/Request.php` remains a separate product-code remediation change.
- Next delivery decision: either commit/push the `Request.php` remediation with package rebuild evidence, or explicitly discard/defer it.

## 2026-07-11 Request Delivery Handoff

- `Request.php` remediation is committed and pushed as `ee582cd51db5b5572d0d291ed7214beed73dd021`.
- `HEAD` and `origin/main` match; working tree is clean.
- Current release package: `.packages/WT Otpravkapochtaru_3.0.0.zip`, SHA-256 `99EBAD7F571B80DAFBDE5A333A1DF66D317D2723BC25AC3723EB07185834E083`.
- Archive proof: 48 entries, updated `Request.php` matches source, no deprecated CMS HTTP factory import, no `docs/`, `.webtolk/` or `.packages/` entries.
- Runtime install/update smoke was not rerun in this slice; evidence is static QA plus package inspection.

## 2026-07-11 SW JProjects Update Handoff

- SW JProjects project ID `119` exists on `web-tolk.ru`.
- Project is unpublished and not visible on the frontend; element is `lib_wt_otpravkapochtaru`.
- Package manifest contains update/changelog URLs with `debug=1`, matching the unpublished-project links generated by the form.
- Published commit: `0596f132efbf1af6e9baff0021604541fcb08024`.
- Current package SHA-256: `567BC3416686FE7FC04BF8886282E82D79608FE619D136449C83AA828B40E86F`.
- Update URL responds with empty `<updates/>`; create a version/changelog record in SW JProjects before expecting the changelog URL to return XML instead of 404.

## 2026-07-11 Publication Documentation Handoff

- Local folder: `.webtolk/tmp/swjprojects-publication-docs-20260711/`.
- Russian source/HTML: `publication-docs-ru.md`, `publication-docs-ru.html`.
- English source/HTML: `publication-docs-en.md`, `publication-docs-en.html`.
- Structure audit: `official-structure-comparison.md`.
- Payload handoff: `publication-payload.json`.
- Nothing has been published to SW JProjects; next operator should paste or import the HTML manually after visual review.

## 2026-07-11 Day-Close Handoff

- Stop point: SW JProjects project exists, screenshots are uploaded, publication documentation is prepared locally, and development-flow artifacts are filled.
- Do not assume full official Otpravka API coverage; unsupported areas are documented in `official-structure-comparison.md`.
- Do not publish the prepared HTML unless the user explicitly requests it.
- For the next session, start by checking `git status --short`, then read `publication-payload.json` and this handoff.

## 2026-07-25 Re-Entry Handoff

- Re-entry completed through `.webtolk` development flow with Joomla platform knowledge loaded.
- Verified current state: clean `main`, `HEAD == origin/main == 0596f132efbf1af6e9baff0021604541fcb08024`.
- Current package: `.packages/WT Otpravkapochtaru_3.0.0.zip`, SHA-256 `567BC3416686FE7FC04BF8886282E82D79608FE619D136449C83AA828B40E86F`.
- Stop point remains the 2026-07-11 local publication handoff: `.webtolk/tmp/swjprojects-publication-docs-20260711/publication-payload.json` plus RU/EN Markdown and HTML fragments.
- Product code changed in this re-entry pass: no.
- Required before remote publication: visual review of the HTML in the SW JProjects editor and explicit user instruction to publish/import.
- Required before complete update/changelog verification: create a version/changelog record in SW JProjects; the existing project metadata commit only added URLs and currently documents the changelog 404 boundary.

## 2026-07-25 Order Tracking Handoff

- Fresh test order remains available for inspection:
  - order number: `codex-order-tracking-20260725_183153`
  - order id: `2333724273`
  - barcode/RPO: `80214523462306`
- Full local evidence: `.webtolk/tmp/order-tracking-check-20260725/summary.json`.
- Assurance report: `.webtolk/docs/reports/order-tracking-runtime-assurance-20260725.md`.
- Confirmed: plugin tracking credentials are present and `getOperationsByRpo()` works against the created barcode.
- Not fully covered: `getOperationsByTicket()` because `getTickets()` returned no ticket and listed the barcode in `not_create`.
- Next skill if requested: `code-assurance` for batch-ticket behavior or runtime cleanup for deleting the test order.

## 2026-07-25 Package Rebuild Handoff

- Current package: `.packages/WT Otpravkapochtaru_3.0.0.zip`.
- SHA-256: `D5AEE6C1373E5CE72CDFF531F6CA13274267FCCF0734E183CDE5B94AC193484E`.
- Archive proof: 48 entries, version `3.0.0`, update/changelog metadata present, no forbidden `.webtolk`, `.packages`, `docs`, `.git`, `.idea`, `.serena` or `.playwright-mcp` entries.
- Packaging note: `.playwright-mcp/` was added to `.webtolk/build/package.config.json` excludes after the first rebuild inspection found a console log in the ZIP.

## 2026-07-25 PHPDoc Cleanup Handoff

- Product source now has Joomla-style PHPDoc for the inspected method/property gaps.
- Current package: `.packages/WT Otpravkapochtaru_3.0.0.zip`.
- SHA-256: `FA4B34B34C826DDE56481D761FD280E7AE19E6C1D0D5DC43D60177CFD16EBF53`.
- Archive proof: 46 entries; all changed PHP files are present; no process/docs/cache directories and no `.phpunit.result.cache`.
- Verification passed: direct PHP lint helper, PHPCS, PHP CS Fixer dry-run, PHPStan and PHPUnit `3 tests / 4 assertions`.
- Tooling note: `composer` launcher fails with `Could not open input file: \composer.phar`; run the underlying PHP QA commands directly until the launcher is fixed.
- Remaining PhpStorm weak warnings are unrelated to PHPDoc cleanup or conflict with project CS rules: duplicate classes from ignored `.webtolk/tmp`/legacy build copies, redundant casts, one polymorphic `Factory::getApplication()` hint and FQN import suggestions.
- Next routing: commit/push only on explicit user request; live Joomla install/update smoke is optional if package delivery is requested.

## 2026-07-25 PHPDoc Delivery Handoff

- PHPDoc cleanup is committed and pushed as `541a8e9d9af39f199c0274c837eb8b901fa27865`.
- `HEAD` and `origin/main` match after push.
- Current local release ZIP remains `.packages/WT Otpravkapochtaru_3.0.0.zip`, SHA-256 `FA4B34B34C826DDE56481D761FD280E7AE19E6C1D0D5DC43D60177CFD16EBF53`.
- Working tree has no tracked changes after the push.
- Next routing: live Joomla install/update smoke only if the user wants runtime delivery proof for the rebuilt ZIP.
