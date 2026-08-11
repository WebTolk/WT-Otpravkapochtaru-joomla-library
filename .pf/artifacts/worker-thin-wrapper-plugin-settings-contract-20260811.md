# Plugin settings compatibility contract — Thin wrapper migration (2.0.1 → 3.0.0)

Task: `t20-plugin-settings-contract`
Run: `lapaygroup-thin-wrapper-migration-20260811`
Scope: System plugin settings only (`plg_system_wt_otpravkapochtaru/**`, `script.php`, `CredentialsProvider.php`).

## Current plugin parameter keys (manifest)

The current plugin field definitions in `plg_system_wt_otpravkapochtaru/wtotpravkapochtaru.xml` expose:

1. `plugin_info`
2. `account_info`
3. `AccessToken`
4. `user_key_or_login_and_password`
5. `user_auth_key`
6. `user_login`
7. `user_password`
8. `tracking_login`
9. `tracking_password`
10. `http_timeout`
11. `linked_test_shipping_point`
12. `linked_test_mail_type`
13. `linked_test_mail_category`

Note: `plugin_info` and `account_info` are UI-only informational fields and are not used by runtime provider credentials logic.

## Legacy accepted keys (backward compatibility)

The migration must keep plugin settings readable when only legacy keys exist:

1. `access_token` (lowercase legacy alias) as token source
2. `AccessToken` (current manifest field)
3. `user_key_or_login_and_password` (legacy alias for auth mode)
4. `auth_mode` (canonical preferred auth mode key in provider)
5. `user_auth_key` (legacy alias for user key in provider)
6. `user_key` (canonical key expected in thin-wrapper flow)
7. `user_login`
8. `user_password`
9. `tracking_login`
10. `tracking_password`
11. `http_timeout`

Legacy support required in implementation logic:
- `CredentialsProvider::getAccessToken()` must continue to resolve token in this order:
  1. `access_token`
  2. `AccessToken`
- `CredentialsProvider::getAuthMode()` must continue to resolve auth mode in this order:
  1. `auth_mode`
  2. `user_key_or_login_and_password`
  3. default `key`
- `CredentialsProvider::getUserKey()` must continue to resolve key in this order:
  1. `user_key`
  2. `user_auth_key`

No additional legacy public PHP API compatibility layer is required.

## New normalized LapayGroup config shape

The effective runtime config for thin-wrapper consumers must be normalized to:

```php
[
  'auth' => [
    'otpravka' => [
      'token' => '<access token>',
      'key' => '<auth key>',
    ],
    'tracking' => [
      'login' => '<tracking login>',
      'password' => '<tracking password>',
    ],
  ],
  'timeout' => [
    'http' => <http_timeout>,
  ],
]
```

Resolution rules (required):
- `auth.otpravka.token` = value from `access_token` fallback `AccessToken`.
- `auth.otpravka.key` = resolved auth key:
  - key mode: value from `user_key` fallback `user_auth_key`;
  - login/password mode: derived from `user_login` + `user_password` (base64/Basic) where applicable by existing provider semantics.
- `auth.tracking.login` = `tracking_login`.
- `auth.tracking.password` = `tracking_password`.
- `http_timeout` should propagate to wrappers that read timeout for request options.

## Exact migration/update invariants

1. **Plugin row continuity**
   - Existing `#__extensions` row for `plg_system_wt_otpravkapochtaru` must remain (same extension row/record identity).
   - Plugin must stay installed and enabled after upgrade.

2. **Version bump**
   - Installed plugin `version` in `#__extensions` must move from `2.0.1` to `3.0.0` (thin-wrapper package).

3. **Settings preservation**
   - `#__extensions.params` must retain existing keys when present, including legacy keys.
   - For recognized values, migration logic must not delete/rename legacy runtime fields except as explicitly documented.

4. **Read path compatibility**
   - Runtime must work if only legacy field names are present.
   - If both canonical and legacy names are present, precedence must match provider behavior:
     - token: `access_token` first, `AccessToken` second;
     - auth mode: `auth_mode` first, `user_key_or_login_and_password` second;
     - user key: `user_key` first, `user_auth_key` second.

5. **Installer behavior invariants**
   - Existing `install()`/`discover_install()`/`update()` and cleanup logic in `script.php` must continue to remove obsolete `plg_system_webtolk_otpravkapochtaru` extension entries and `pkg_webtolk_otpravka_russianpost` package entries.
   - If cleanup fails, install/update must fail (preserve migration gate).

6. **Environment constraints**
   - Update path remains constrained to min PHP/Joomla levels already declared by script checks; migration is not allowed to bypass these checks.

## Installer/update checks needed

1. **Preflight/update guard checks**
   - Enforce PHP/Joomla minimum versions exactly as current `script.php` does.
   - Abort on failure with installer abort semantics.

2. **Legacy package/extension cleanup checks**
   - On install/update/discover:
     - remove old extension records and any corresponding extension package rows as required;
     - verify cleanup query executed and plugin row integrity remains valid.

3. **Param passthrough checks**
   - During/after update, verify that plugin params JSON still contains:
     - `AccessToken` and/or `access_token`,
     - `user_auth_key` and/or `user_key_or_login_and_password`,
     - `user_login`/`user_password`,
     - `tracking_login`/`tracking_password`,
     - `http_timeout`.

4. **Post-install plugin-state checks**
   - plugin stays in enabled state;
   - plugin remains visible in extension manager and can be opened with settings fields populated from stored params.

## Tests needed to prove parameter preservation

1. **Unit tests (existing + keep)**
   - `tests/Unit/Configuration/CredentialsProviderTest.php`
     - canonical-only read paths,
     - legacy-only read paths for `AccessToken`, `user_key_or_login_and_password`, `user_auth_key`,
     - precedence rule for canonical-over-legacy where applicable.

2. **Upgrade/install integration test**
   - Install baseline `2.0.1` package, set both legacy and canonical fields, run package update to `3.0.0`.
   - Assert plugin `extension_id` unchanged, `enabled=1`, `version=3.0.0`.
   - Assert params contain at least the original keys above; no unexpected deletion/flattening of plugin setting values.

3. **Runtime readback test after upgrade**
   - Read plugin params from DB and hydrate through `CredentialsProvider`; verify token and key/tracking/auth mode are resolved from legacy values if canonical absent.

4. **Cleanup + migration gate test**
   - Confirm `script.php` cleanup is executed on update and does not remove plugin params.
   - Confirm failure case for cleanup leaves update blocked (no half-migrated settings state).

5. **UI preservation smoke**
- Load plugin settings form after update and confirm legacy fields are still rendered and populated from stored params.
