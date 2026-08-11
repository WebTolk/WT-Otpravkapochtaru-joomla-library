# Worker Smoke Report
Run ID: `lapaygroup-joomla-core-swap-20260811`
Task ID: `t15-lapaygroup-core-swap-sdk-smoke`
Requested Mode: read-only runtime smoke

- Timestamp start: `2026-08-11T08:34:32+04:00`
- Timestamp end: `2026-08-11T08:34:40+04:00`
- Runtime: `8176 ms`

## Commands Executed
- `php {OSPanel PHP-8.3}\php.exe .pf\tmp\lapaygroup-core-swap-sdk-smoke\smoke.php`

## Step Results

1. Plugin params source (`#__extensions` in Joomla DB)
- PASS
- SQL target: `type='plugin' AND folder='system' AND element='wtotpravkapochtaru' AND enabled=1`
- Plugin parameter count: `5`

2. SDK autoload from Joomla core/vendor
- PASS
- Resolved class: `LapayGroup\\RussianPost\\Http\\Psr18Transport`
- Resolved file path: `D:\OSPanel\home\joomla.local\public\libraries\vendor\lapaygroup\russianpost\src\Http\Psr18Transport.php`
- Autoload source: vendor autoload only (`D:\OSPanel\home\joomla.local\public\libraries\vendor\autoload.php`)

3. Transport build prerequisites
- PASS
- Client class: `Joomla\\Http\\Http`
- Factories: `Laminas\\Diactoros\\RequestFactory`, `Laminas\\Diactoros\\StreamFactory`, `Laminas\\Diactoros\\UploadedFileFactory`

4. Instantiation check
- PASS
- `LapayGroup\\RussianPost\\Providers\\OtpravkaApi` instantiated with constructor signature: `__construct(array $config, Psr18Transport $httpTransport)`
- `LapayGroup\\RussianPost\\Providers\\Calculation` instantiated with constructor signature: `__construct(Psr18Transport $httpTransport)`

## Plugin params (sanitized)

- `AccessToken`: present, length `40`
- `user_key_or_login_and_password`: present, length `3`
- `user_auth_key`: present, length `36`
- `user_key`: missing
- `user_login`: present, length `20`
- `user_password`: present, length `36`
- `tracking_login`: missing
- `tracking_password`: missing
- `http_timeout`: missing
- `linked_test_shipping_point`: missing
- `linked_test_mail_type`: missing
- `linked_test_mail_category`: missing

Sanitized auth summary:
- `access_token_present`: true
- `access_token_length`: `40`
- `auth_mode`: `key`
- `user_auth_key_length`: `36`
- `auth_key_length`: `36`
- `user_login_present`: true
- `user_password_present`: true

## API Calls (read-only)

| Call | Status | Notes |
| --- | --- | --- |
| settings/account (`OtpravkaApi::settings`) | FAIL | Runtime connection error: `Failed to connect to otpravka-api.pochta.ru port 443 via 127.0.0.1 after ~2s` |
| shipping points (`OtpravkaApi::shippingPoints`) | FAIL | Runtime connection error: `Failed to connect to otpravka-api.pochta.ru port 443 via 127.0.0.1 after ~2s` |
| postoffice lookup (`OtpravkaApi::searchPostOfficeByIndex`) | FAIL | Runtime connection error: `Failed to connect to otpravka-api.pochta.ru port 443 via 127.0.0.1 after ~2s` (postal code attempted: `101000`) |
| tariff calculation (`Calculation::getTariff`) | FAIL | Runtime connection error: `Failed to connect to delivery.pochta.ru port 443 via 127.0.0.1 after ~2s` |

## Blockers
- Outbound HTTPS to `127.0.0.1:443` for both API hosts is blocked (`Could not connect to server`).
- No mutation API calls were performed; no orders or account state changes were attempted.
- Allowed writes were limited to `.pf/tmp/...` and `.pf/artifacts/...`.
