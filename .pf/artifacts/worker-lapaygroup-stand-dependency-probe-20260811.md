# Worker Artifact: t07-lapaygroup-stand-dependency-probe
run_id: lapaygroup-russianpost-joomla-local-validation-20260811

## Goal
Verify whether `joomla.local` can host `lapaygroup/russianpost:2.0.0` with Joomla-way dependencies.

## Checks Executed
- `php -v`
- `Get-Content` / `Test-Path` for `D:\OSPanel\home\joomla.local\public\libraries\vendor\autoload.php`
- `Test-Path` for PSR/PSR-18/PSR-17/Diactoros/Joomla HTTP classes and `installed.php` in Joomla vendor
- PHP reflection/`interface_exists` checks via isolated script at `.pf\tmp\lapaygroup-stand-dep-probe\final_check.php`
- `composer --version`
- `composer install` in isolated scratch dir `.pf\tmp\lapaygroup-russianpost-install-probe`

## Environment Snapshot
- PHP version: `8.3.30`
- Composer version: `2.9.5`
- `openssl` PHP extension: **missing** (`php -m | rg -n '^openssl$'` returned no match)

## Dependency checks (PASS/FAIL)
| Check | Status |
| --- | --- |
| PHP version `>= 8.3` | PASS |
| Joomla vendor autoload exists (`.../libraries/vendor/autoload.php`) | PASS |
| `Psr\Http\Client\ClientInterface` exists | PASS |
| `Psr\Http\Message\RequestFactoryInterface` exists | PASS |
| `Psr\Http\Message\StreamFactoryInterface` exists | PASS |
| `Psr\Http\Message\UploadedFileFactoryInterface` exists | PASS |
| `Joomla\Http\Http` class exists | PASS |
| `Joomla\Http\Http` usable as `Psr\Http\Client\ClientInterface` | PASS |
| `Laminas\Diactoros\RequestFactory` exists | PASS |
| `Laminas\Diactoros\StreamFactory` exists | PASS |
| `Laminas\Diactoros\UploadedFileFactory` exists | PASS |

## Dependency versions from `libraries/vendor/composer/installed.php`
- `psr/http-client`: `1.0.3.0`
- `psr/http-factory`: `1.1.0.0`
- `laminas/laminas-diactoros`: `3.8.0.0`
- `psr/http-message`: `2.0.0.0`
- `joomla/http`: `4.0.2.0`

## `lapaygroup/russianpost:2.0.0` install probe
- Command: `composer -d .pf\tmp\lapaygroup-russianpost-install-probe install --no-interaction --no-progress --no-plugins --prefer-dist --no-scripts`
- Result: **FAIL**
- Blocker: Composer bootstrap fails before dependency resolution: `The openssl extension is required for SSL/TLS protection but is not available.`
- Also observed `--disable-tls` is not available in this Composer executable, so install cannot proceed in this environment.

## Outcome
- Overall host dependency readiness for runtime checks: **PASS**
- `lapaygroup/russianpost:2.0.0` install/download into isolated scratch: **BLOCKED** by environment (`openssl` PHP extension unavailable in CLI).

## Commands log (raw sequence)
1. `composer --version`
2. `php -d`-style probe script `.pf\tmp\lapaygroup-stand-dep-probe\final_check.php`
3. `composer -d .pf\tmp\lapaygroup-russianpost-install-probe install --no-interaction --no-progress --no-plugins --prefer-dist --no-scripts`
