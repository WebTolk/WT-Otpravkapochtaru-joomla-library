# JoomShopping Surface Check
- Assignment: t16-lapaygroup-core-swap-joomshopping-surface
- run_id: lapaygroup-joomla-core-swap-20260811
- mode: read-only
- date: 2026-08-11

## Commands executed
- PowerShell
  - `rg -l "wtotpravkapochtaru|LapayGroup|russianpost|jshop" 'D:\OSPanel\home\joomla.local\public\plugins' -g '*.xml'`
  - `php 'D:\OSPanel\home\joomla.local\public\cli\joomla.php' extension:list --enabled --type=plugin --short`
  - `& 'D:\OSPanel\modules\MariaDB-11.8\bin\mysql.exe' -h mariadb-11.8.local -P3306 -uroot --skip-ssl joomla.local -e "SELECT extension_id,folder,element,enabled,manifest_cache FROM l5jfq_extensions WHERE type='plugin' AND element IN ('wtotpravkapochtaru','wtjshopotpravkapochtaru');"`
  - `Get-Content`/`Get-ChildItem` on logs and plugin manifests/assets
  - `Get-ChildItem 'D:\OSPanel\home\joomla.local\public\media\plg_system_wtotpravkapochtaru'`
  - temporary CLI field render check script: `.pf/tmp/check_plugin_form_surface.php`
- curl
  - `curl.exe --noproxy '*' -sS -o .\.pf\tmp\jshop_ajax_getMailTypes.txt -w "HTTP_STATUS=%{http_code}" ...`

## Surface checks

### 1) Required system plugins installed & enabled
**PASS**

SQL check against `joomla.local.l5jfq_extensions`:

- `wtjshopotpravkapochtaru` � `enabled=1`, `folder=system`, `extension_id=336`
- `wtotpravkapochtaru` � `enabled=1`, `folder=system`, `extension_id=389`

Source: MariaDB query.

### 2) Joomla logs / PHP error logs snapshot
**PASS (no new plugin-surface errors observed during check window)**

#### 2a) `administrator/logs/1.error.php`
**before**
```
2026-07-31T08:16:00+00:00	INFO	127.0.0.1	joomlafailure	Логин ...
```
**after**
```
2026-07-31T08:16:00+00:00	INFO	127.0.0.1	joomlafailure	Логин ...
```
(no new lines between snapshots)

#### 2b) `administrator/logs/1.joomla_update.php`
No plugin-surface-related entries before/after (history-only update events from 2026-07-01).

#### 2c) PHP error log for domain
`D:\OSPanel\logs\domains\joomla.local_error.log`
- **before snapshot**: `Size=0`, `LastWrite=08/10/2026 08:01:33`
- **after snapshot**: `Size=0`, `LastWrite=08/10/2026 08:01:33`

### 3) JoomShopping addon/system plugin form surface (CLI)
**PARTIAL PASS**

- A dedicated script successfully discovered both plugin manifests and parsed fields.
- Field parsing for `wtotpravkapochtaru` returned field types including:
  - Plugininfo
  - accountinfo
  - text/list/password
  - opslist
  - mailtypes
  - mailcategories
- Field parsing for `wtjshopotpravkapochtaru` returned:
  - Plugininfo
  - create_pochta_ru_order_by
  - jshoppingorderstatus
  - debug
- Script reported `form_instantiated` for both plugins (Joomla `Form` object creation from generated XML).

Boot limitation noted:
- CLI attempt to initialize full application/web asset manager failed with
`Exception: Failed to start application` in environment (Joomla CLI DB dependency unavailable).

### 4) WebAsset field assets availability
**PASS**

- Plugin manifest `wtotpravkapochtaru.xml` declares media asset package `media` -> `plg_system_wtotpravkapochtaru`.
- Actual published media exists at:
  - `public/media/plg_system_wtotpravkapochtaru/joomla.asset.json`
  - `public/media/plg_system_wtotpravkapochtaru/js/linked-select-fields.js`
- `joomla.asset.json` contains asset `plg_system_wtotpravkapochtaru.linked-select-fields`.

### 5) Runtime/browser rendering via local HTTP
**BLOCKED**

- Attempts to open `http://joomla.local/...` and `http://127.0.0.1/joomla/...` from worker failed:
  - `curl` could not connect (local web endpoint unavailable in this environment).
- Because of that, no browser screenshot could be captured.

## Blockers
1. **CLI environment mismatch**: `php cli/joomla.php ...` fails with `Joomla\Database\Exception\UnsupportedAdapterException: The MySQLi extension is not available`.
2. **No local HTTP endpoint available** in this worker session for page-level rendering.

## Command-level artifacts
- temp script: `.pf/tmp/check_plugin_form_surface.php`
- temp curl output target: `.pf/tmp/jshop_ajax_getMailTypes.txt` (not created due network/connect blocker)

## Outcome
- Plugin install/enable checks: PASS
- Form metadata parsing and form construction checks: PARTIAL PASS
- WebAsset manifest check: PASS
- End-to-end admin UI verification: BLOCKED by environment
- No product files were changed; no plugin params modified.
