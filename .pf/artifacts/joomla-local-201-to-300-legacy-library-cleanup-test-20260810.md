# Joomla Local 2.0.1 -> 3.0.0 Legacy Library Cleanup Test

Timestamp: 2026-08-10 22:27:26 +04:00

## Scope

Validate the installer script cleanup for the pre-3.0 legacy Joomla library
element `Webtolk/Pochtaru` during an update path from the 2.0.1 package in
`.pf/tmp` to the current 3.0.0 package.

## Product Change

Changed `script.php`:

- `update()` now calls `removeLegacyPochtaruLibrary()`.
- `preflight()` also calls `removeLegacyPochtaruLibrary()` for `install` and
  `discover_install`.
- The `preflight()` hook is required for the real 2.0.1 -> 3.0.0 transition
  because Joomla treats `pkg_lib_wt_otpravkapochtaru` as a new package install
  after the package element changed from `pkg_smwtotpravkapochtaru`.
- The cleanup uses Joomla's native installer API:
  `Installer::getInstance()->uninstall('library', $extensionId)`.
- If the old library cannot be removed, installation/update returns `false`
  after enqueueing an installer error.

## Package

Built with:

`phing -f "D:\Dev\WT-Otpravkapochtaru-joomla-library\phing.xml" "3. Package release"`

Resulting package:

`.packages/WT Otpravkapochtaru_3.0.0.zip`

Package inspection:

- Entries: `41`
- Bytes: `61882`
- SHA-256: `AD359B3B59D5C359A130EDC49A19619E13FF4328C0176DE468CC7C9158F5FCCB`
- Forbidden entries: `0`
- `script.php` inside the ZIP contains the `Installer` import,
  `Webtolk/Pochtaru`, and the update cleanup call.

## QA

- PHP lint: passed.
- PHPUnit: `OK (10 tests, 25 assertions)`.
- JS syntax: passed.
- PHPCS: passed.
- PHPStan: passed.
- PHP CS Fixer dry-run: passed with no file changes.

## Runtime Procedure

1. Removed current 3.0.0 package from `joomla.local`:
   `extension:remove 387`.
2. Confirmed remaining baseline:
   - `pkg_smwtotpravkapochtaru` 2.0.1
   - `Webtolk/Pochtaru` 2.0.1
3. Installed `.pf/tmp/pkg_smwtotpravkapochtaru_2.0.1.zip`.
4. Confirmed 2.0.1 baseline:
   - `Webtolk/Pochtaru` library: `extension_id=388`, version `2.0.1`
   - `wtotpravkapochtaru` plugin: `extension_id=389`, version `2.0.1`
5. Enabled plugin `389` and saved deterministic legacy params:

```json
{
  "AccessToken": "codex-access-token-cleanup-test-20260810",
  "user_key_or_login_and_password": "key",
  "user_auth_key": "codex-user-key-cleanup-test-20260810",
  "user_login": "cleanup@example.test",
  "user_password": "codex-password-cleanup-test-20260810"
}
```

6. Installed `.packages/WT Otpravkapochtaru_3.0.0.zip`.

## Result

Passed.

After installing 3.0.0:

- `Webtolk/Pochtaru` legacy library count in `#__extensions`: `0`.
- `wtotpravkapochtaru` kept the same plugin row: `extension_id=389`.
- `wtotpravkapochtaru` manifest version became `3.0.0`.
- `wtotpravkapochtaru` remained enabled.
- `Webtolk/Otpravkapochtaru` 3.0.0 was installed as the current library:
  `extension_id=390`.
- `pkg_lib_wt_otpravkapochtaru` 3.0.0 was installed:
  `extension_id=391`.
- Legacy plugin params remained intact under the existing keys.
- The Joomla administrator plugin form showed the retained values.
- Installed `CredentialsProvider` 3.0.0 read the retained values in Joomla CLI
  application context:
  - `getAccessToken()` -> `codex-access-token-cleanup-test-20260810`
  - `getAuthMode()` -> `key`
  - `getUserKey()` -> `codex-user-key-cleanup-test-20260810`
  - `getUserAuthorizationHeader()` ->
    `codex-user-key-cleanup-test-20260810`

## Residual State

The JoomShopping package line remains installed as expected:

- `pkg_smwtotpravkapochtaru` 2.0.1
- `sm_wt_otpravka_pochta_ru` 2.0.1
- `wtjshopotpravkapochtaru` 2.0.1

The old shared library `Webtolk/Pochtaru` no longer remains after the 3.0.0
installation.
