# Joomla Local 2.0.1 -> 3.0.0 Plugin Params Upgrade Test

Timestamp: 2026-08-10 22:19:24 +04:00

## Scope

Validate that existing system plugin parameters survive an update from the
2.0.1 package in `.pf/tmp` to the current 3.0.0 package.

## Packages

- Source 2.0.1 package:
  `.pf/tmp/pkg_smwtotpravkapochtaru_2.0.1.zip`
- Target 3.0.0 package:
  `.packages/WT Otpravkapochtaru_3.0.0.zip`

## Procedure

1. Removed the currently installed 3.0.0 library package from `joomla.local`:
   `extension:remove 318`.
2. Confirmed only the JoomShopping integration package remained installed.
3. Installed `.pf/tmp/pkg_smwtotpravkapochtaru_2.0.1.zip`.
4. Confirmed the 2.0.1 system plugin used the legacy parameter names:
   `AccessToken`, `user_key_or_login_and_password`, `user_auth_key`,
   `user_login`, `user_password`.
5. Enabled the 2.0.1 system plugin and saved deterministic test values into
   its plugin params.
6. Installed `.packages/WT Otpravkapochtaru_3.0.0.zip` over that state.
7. Checked the database row, the Joomla administrator plugin form, and runtime
   reading through `CredentialsProvider`.

## Control Values

The 2.0.1 system plugin record was set to:

```json
{
  "AccessToken": "codex-access-token-upgrade-test-20260810",
  "user_key_or_login_and_password": "key",
  "user_auth_key": "codex-user-key-upgrade-test-20260810",
  "user_login": "codex@example.test",
  "user_password": "codex-password-upgrade-test-20260810"
}
```

## Result

Passed.

After installing 3.0.0:

- The system plugin kept the same database row: `extension_id=385`.
- The system plugin manifest version changed from `2.0.1` to `3.0.0`.
- The system plugin remained enabled.
- The `params` JSON retained all control values under the legacy field names.
- The Joomla administrator plugin form showed the retained values in the 3.0.0
  fields.
- `CredentialsProvider` 3.0.0 read the legacy keys successfully:
  - `getAccessToken()` returned `codex-access-token-upgrade-test-20260810`.
  - `getAuthMode()` returned `key`.
  - `getUserKey()` returned `codex-user-key-upgrade-test-20260810`.
  - `getUserAuthorizationHeader()` returned
    `codex-user-key-upgrade-test-20260810`.

## Residual Observation

The update installs the new 3.0.0 library package beside the old
JoomShopping package:

- `pkg_smwtotpravkapochtaru` remains at `2.0.1`.
- `Webtolk/Pochtaru` remains at `2.0.1`.
- `wtotpravkapochtaru` is updated in place to `3.0.0`.
- `Webtolk/Otpravkapochtaru` is installed as the new 3.0.0 library.
- `pkg_lib_wt_otpravkapochtaru` is installed as the new 3.0.0 package.

This does not break plugin parameter preservation, but it is a separate package
lineage/cleanup consideration.
