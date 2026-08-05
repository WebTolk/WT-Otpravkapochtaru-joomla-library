# Test Coverage Proposal - 2026-07-11

## Goal

Increase assurance for transport, credentials, tracking, installer, and Joomla fields without coupling unit tests to live Russian Post services.

## Recommended Order

### P0 - REST transport contract

- Add an injectable `Joomla\Http\Http` client or small client factory seam to `Request`.
- Test GET/POST/PUT/DELETE targets, headers, JSON encoding, non-JSON responses, HTTP errors, and business errors.
- Test `Content-Disposition` parsing and safe filename cases: traversal, Windows separators, control characters, reserved device names, empty values, RFC 5987, and Unicode.
- Keep all tests deterministic; do not call live endpoints.

### P0 - Credentials

- Cover user-key and login/password modes.
- Cover legacy parameter aliases.
- Verify missing values fail before network calls.
- Verify exception messages never contain access tokens, keys, logins, or passwords.

### P1 - SOAP tracking

- Introduce a replaceable SOAP client/factory boundary.
- Verify single and pack payload shape without a live WSDL.
- Cover 500-item chunking, empty ticket responses, result normalization, and exception wrapping.
- Add a regression check that production mode does not retain or log credential-bearing trace data if that finding is later remediated.

### P1 - Joomla integration

- Run install/update/uninstall smoke on Joomla 5.4 and current Joomla 6.
- Verify plugin auto-enable behavior and parameter preservation.
- Render custom fields with fake transport responses and verify escaping/error states.
- Check manifest namespace/service-provider loading on both Joomla versions.

### P2 - Compatibility matrix

- Run the suite on PHP 8.1 and the current development runtime.
- Run PHPStan against both Joomla 5.4 and Joomla 6.1 source roots.
- Raise PHPStan gradually from level 1 after framework/bootstrap noise is isolated.

## Acceptance Criteria

- Transport, credentials, and tracking tests never require network access.
- Every public transport failure maps to an asserted library-level exception contract.
- Joomla 5.4 and Joomla 6.x installation smoke pass from the generated ZIP.
- Security transformations have explicit regression cases.
- Coverage is measured per risk-bearing module rather than by a repository-wide percentage alone.

## Current Decision

- Proposal only. No permanent test suite changes were made in the two-finding remediation slice.
- A scratch verifier under `.webtolk/tmp/verify/` covered the filename fix without expanding task scope.
