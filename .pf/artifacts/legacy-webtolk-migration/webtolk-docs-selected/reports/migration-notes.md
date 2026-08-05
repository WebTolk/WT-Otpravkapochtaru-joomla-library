# Migration Notes

## 2026-07-11 Version 3.0.0 Notes
- Install or update package `.packages/WT Otpravkapochtaru_3.0.0.zip`.
- Package SHA-256 for the current local release candidate: `B20137F93DBCDBA12F27D063FA569E8753537DF8FAE219BAB66AB1350F56C9E2`.
- The package includes the library, system plugin, root `script.php`, package sys language files, and plugin language files.
- The installer script uses Joomla message queue output, not direct `echo`/`print`.
- The system plugin remains the configuration surface for Russian Post credentials; valid tracking SOAP credentials are required before tracking verification can pass.
- No database schema migration is currently recorded for this release.
- The final package includes the documentation and Composer platform-requirement update committed as `d1e24d6`.

## 2026-07-11 Local Remediation Package

- Current rebuilt package SHA-256: `C68F71A6F4D6B7E207CDF8684C11BFA457D7A1934C2D90BACC5DD1A767E22C1A`.
- No configuration, schema, credential, or public facade migration is required.
- Consumers that persist `getBinary()['fileName']` receive a safer normalized basename.

## When Migration Is Required
- Package install or update is required to pick up the reduced public facade.
- No data migration is required in this cycle.

## Preconditions
- Joomla instance must allow package install/update and browser access to the local verification wrapper.
- Valid live API credentials must remain configured in the installed plugin parameters.

## Steps
1. Backup current extension state and DB.
2. Install or copy the current library/package build to the Joomla stand.
3. Open `http://joomla.local/tmp/wt_otpravkapochtaru_api_sweep.php`.
4. Confirm the summary is `16 ok / 0 error / 14 skipped`.
5. Verify that skipped methods are limited to mutation-disabled operations in the read-only runner.

## Backward Compatibility
- Public facade coverage is intentionally smaller than donor coverage in this release.
- Verified methods remain usable on the current live API surface.
- Dead donor-era methods have been removed from the active verified surface.

## Data Or Config Impact
- Existing credentials are reused.
- No schema or stored configuration changes are introduced.
- `getCountryList()` continues to be served from local official reference data rather than a remote runtime endpoint.

## Rollback Strategy
- Reinstall the previous package version if the remediated facade causes regressions.
- Restore the old methods only if you intentionally accept an unverified donor-era API surface.

## Toolchain Contract References
- Browser sweep wrapper on `joomla.local`
- `php` lint for modified source files

## 2026-07-11 API Schema Appendix

- No Joomla installation, database, configuration or package migration is required for `docs/api-schemas/otpravka/`.
- The appendix is repository documentation only and does not ship in the Joomla package.
- Raw API captures are local research data under ignored `.webtolk/tmp/` and must not be committed or copied into release archives.

## 2026-07-11 Technical Documentation

- No Joomla, database, configuration or package migration is required.
- The change affects repository documentation only.
- Existing integrations should review corrected examples for `Recipient`, batch responses and post-office address search before copying old snippets.
- Current rebuilt package SHA-256: `6B5B7746D97FCECA754A4E237A4AE18B505AD9674F5D801FFD70615FAF30253F`; no migration is introduced by the documentation rebuild.

## 2026-07-25 Package Rebuild

- Current rebuilt package SHA-256: `D5AEE6C1373E5CE72CDFF531F6CA13274267FCCF0734E183CDE5B94AC193484E`.
- No Joomla, database, credential or public API migration is introduced by this rebuild.
- Packaging-only config adjustment: `.playwright-mcp/` is now excluded from release archives.

## 2026-07-25 PHPDoc Cleanup

- Current rebuilt package SHA-256: `FA4B34B34C826DDE56481D761FD280E7AE19E6C1D0D5DC43D60177CFD16EBF53`.
- No Joomla, database, credential, public API or configuration migration is introduced.
- Consumers only need package install/update if they want the refreshed source documentation in the installed files.
