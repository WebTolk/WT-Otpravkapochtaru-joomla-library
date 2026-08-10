# Otpravka API credentials diagnostic

Date: 2026-08-06
Stand: `joomla.local`
Script: `.pf/tmp/diagnose_otpravka_api.php`

## Scope

Checked the system plugin settings stored in the Joomla.local database and made direct Otpravka API calls using the same header shape as the library transport:

- `Authorization: AccessToken <redacted>`
- `X-User-Authorization: Basic <redacted>`
- `Content-Type: application/json`
- `Accept: application/json;charset=UTF-8`

Secrets were not printed.

## Runtime

The default `php` from PATH is not suitable for this stand diagnostic: it lacks `mysqli`, `pdo_mysql`, `curl`, and `openssl`.

The diagnostic was run with OSPanel PHP:

`D:\OSPanel\modules\PHP-8.3\php.exe D:\Dev\WT-Otpravkapochtaru-joomla-library\.pf\tmp\diagnose_otpravka_api.php`

## Plugin Settings Presence

- Plugin installed: yes
- Plugin enabled: yes
- `auth_mode`: `key`
- `access_token`: present, length 32
- legacy `AccessToken`: absent
- `user_key`: present, length 32
- legacy `user_auth_key`: absent
- `user_login`: present, length 16
- `user_password`: present, length 12
- `http_timeout`: 60

## API Calls

Endpoints checked:

- `GET https://otpravka-api.pochta.ru/1.0/settings`
- `GET https://otpravka-api.pochta.ru/1.0/user-shipping-points`

Probes checked without changing saved settings:

- configured mode: `key`
- forced key mode using saved `user_key`
- forced login/password mode using saved `user_login:user_password`

All probes returned the same API response:

- HTTP status: `401 Unauthorized`
- JSON response: yes
- API code: `1011`
- API desc: `ILLEGAL_CREDENTIALS`
- API sub-code: `UNAUTHORIZED`
- Transport error: none

## Joomla Logs

Relevant Joomla log search in `D:\OSPanel\home\joomla.local\public\administrator\logs` found no Otpravka/API authorization entries. This is consistent with the CLI diagnostic: the request reached the remote API and failed with a normal JSON authorization response, not with a local PHP/Joomla exception.

## Conclusion

The current problem is not the linked-field AJAX code and not network transport. The stand reaches `otpravka-api.pochta.ru` and receives a structured API error.

The saved credentials in the system plugin are rejected by the Russian Post Otpravka API. Both supported authorization variants available in settings fail:

- `AccessToken + user_key`
- `AccessToken + base64(user_login:user_password)`

Next action: replace the Otpravka API credentials on `joomla.local` with a valid matching pair from the same Russian Post account/application, then repeat the diagnostic. After that, `getShippingPoints()` should return the OPS list instead of the current safe AJAX `502`.
