# worker snapshot: t13-lapaygroup-core-swap-stand-snapshot
run_id: lapaygroup-joomla-core-swap-20260811
mode: shell-worker / read-only stand
snapshot_path: D:\OSPanel\home\joomla.local\public
checked_at: 2026-08-11

## Required checks (PASS/FAIL)

- PASS � current installed project library located in Joomla stand.
  - Library root: `D:\OSPanel\home\joomla.local\public\libraries\Webtolk\Otpravkapochtaru`
  - Plugin root: `D:\OSPanel\home\joomla.local\public\plugins\system\wtotpravkapochtaru`

- PASS � Joomla autoload and composer autoload map files located.
  - `D:\OSPanel\home\joomla.local\public\libraries\vendor\autoload.php` (file exists)
  - `D:\OSPanel\home\joomla.local\public\libraries\vendor\composer\autoload_psr4.php` (file exists)
  - `D:\OSPanel\home\joomla.local\public\libraries\vendor\composer\installed.json` (file exists)

- PASS/INFO � no project-level composer vendor at web-root.
  - `D:\OSPanel\home\joomla.local\public\vendor` path: **missing** (PASS for stand inspection expectation: no root vendor)

- PASS � `lapaygroup/russianpost` not present in stand vendor composer artifacts.
  - Search in `libraries\vendor\composer\installed.json`: **no match**
  - Search in `libraries\vendor\composer\autoload_psr4.php`: **no match**
  - Search `D:\OSPanel\home\joomla.local\public\libraries\vendor` for strings `lapaygroup|RussianPost|russianpost`: **no match**

- PASS � local SDK source inspected.
  - Path: `D:\Dev\WT-Otpravkapochtaru-joomla-library\.pf\tmp\LapayGroup-RussianPost-2.0.0\RussianPost-2.0.0`
  - File count: `54`
  - Includes: `composer.json`, `.github/workflows`, `src/*`, `tests/*`

- PASS � plugin params for system plugin confirmed from Joomla DB (`extensions.element='wtotpravkapochtaru'`).
  - DB host from stand config: `mariadb-11.8.local`, user `root`, db `joomla.local`, dbtype `mysqli`
  - Query result keys (sanitized only):
    - `AccessToken`: type=String, len=40, non-empty
    - `user_key_or_login_and_password`: type=String, len=3, non-empty
    - `user_auth_key`: type=String, len=36, non-empty
    - `user_login`: type=String, len=20, non-empty
    - `user_password`: type=String, len=36, non-empty
  - No missing param keys in row.

- PASS � OSPanel PHP executable for `joomla.local` identified and validated for Joomla+DB use.
  - Apache vhost host mapping: `D:\OSPanel\modules\Apache\conf\httpd.conf:803`
    - `Use Host_PHP joomla.local ... "127.0.1.39"`
  - IP map: `D:\OSPanel\system\modules.dat:30` > `PHP-8.3=127.0.1.39`
  - Resolved PHP executable: `D:\OSPanel\modules\PHP-8.3\php.exe`
  - `PHP-8.3` extension check includes: `mysqli`, `PDO`, `pdo_mysql`, `pdo_sqlite`

## Evidence commands used

- `rg --files "D:\OSPanel\home\joomla.local\public" -g "*otpravkapochtaru*"`
- `rg -n "lapaygroup/russianpost" "D:\OSPanel\home\joomla.local\public"`
- `Get-ChildItem -Path "D:\OSPanel\home\joomla.local\public\libraries\vendor" -Recurse -File -Include "autoload.php","autoload_psr4.php","autoload_namespaces.php","autoload_classmap.php","InstalledVersions.php","installed.json"`
- `& 'D:\OSPanel\modules\MariaDB-11.8\bin\mysql.exe' --skip-ssl -h 'mariadb-11.8.local' -u root joomla.local -N -B -e "SELECT element, name, params FROM l5jfq_extensions WHERE type='plugin' AND folder='system' AND element='wtotpravkapochtaru' LIMIT 1;"`
- `rg -n "Use Host_PHP joomla.local" "D:\OSPanel\modules\Apache\conf\httpd.conf"`
- `rg -n "PHP-8.3=127.0.1.39" "D:\OSPanel\system\modules.dat"`
- `& D:\OSPanel\modules\PHP-8.3\php.exe -m | Select-String -Pattern 'mysqli|pdo|pdo_mysql|mysql'`

## Writer backup list (exact)

Before swap, backup these files/dirs from stand:

1. `D:\OSPanel\home\joomla.local\public\libraries\Webtolk\Otpravkapochtaru\`
   - `otpravkapochtaru.xml`
   - `src\Otpravkapochtaru.php`
   - `src\Request.php`
   - `src\SoapRequest.php`
   - `src\TrackingEntity.php`
   - `src\Configuration\CredentialsProvider.php`
   - `src\Dictionaries\CountryDictionary.php`
   - `src\Entity\AbstractEntity.php`
   - `src\Entity\AddressReturn.php`
   - `src\Entity\CustomsDeclaration.php`
   - `src\Entity\CustomsDeclarationItem.php`
   - `src\Entity\EcomData.php`
   - `src\Entity\Item.php`
   - `src\Entity\Order.php`
   - `src\Entity\Recipient.php`
   - `src\Entity\ReturnShipment.php`
   - `src\Exception\ConfigurationException.php`
   - `src\Exception\OtpravkapochtaruException.php`
   - `src\Exception\TrackingException.php`
   - `src\Exception\TransportException.php`
   - `src\Exception\ValidationException.php`
   - `src\Fields\AccountinfoField.php`
   - `src\Fields\LinkedSelectField.php`
   - `src\Fields\MailcategoriesField.php`
   - `src\Fields\MailtypesField.php`
   - `src\Fields\OpslistField.php`
   - `src\Service\LinkedSelectOptionsService.php`

2. `D:\OSPanel\home\joomla.local\public\plugins\system\wtotpravkapochtaru\`
   - `wtotpravkapochtaru.xml`
   - `services\provider.php`
   - `src\Extension\Wtotpravkapochtaru.php`
   - `src\Field\PlugininfoField.php`
   - `src\Fields\PlugininfoField.php`

3. `D:\OSPanel\home\joomla.local\public\administrator\manifests\packages\`
   - `pkg_lib_wt_otpravkapochtaru.xml`
   - `pkg_smwtotpravkapochtaru.xml`

4. Joomla-library composer autoload map/locking files that will be touched if vendor swap path strategy is needed:
   - `D:\OSPanel\home\joomla.local\public\libraries\vendor\autoload.php`
   - `D:\OSPanel\home\joomla.local\public\libraries\vendor\composer\autoload_psr4.php`
   - `D:\OSPanel\home\joomla.local\public\libraries\vendor\composer\installed.json`
   - `D:\OSPanel\home\joomla.local\public\libraries\vendor\composer\autoload_classmap.php` (if regeneration policy changes)
   - `D:\OSPanel\home\joomla.local\public\libraries\vendor\composer\autoload_namespaces.php` (if regeneration policy changes)

5. `D:\OSPanel\home\joomla.local\public\language\en-GB\pkg_lib_wt_otpravkapochtaru.sys.ini`
6. `D:\OSPanel\home\joomla.local\public\language\ru-RU\pkg_lib_wt_otpravkapochtaru.sys.ini`

## Patch plan for T14 (reversible)

- Step 1 � create backup bundle of items in section above.
- Step 2 � in a separate temp dir, prepare replacement library payload from
  `D:\Dev\WT-Otpravkapochtaru-joomla-library\.pf\tmp\LapayGroup-RussianPost-2.0.0\RussianPost-2.0.0` and validate namespace/class parity against existing `Webtolk\\Otpravkapochtaru` API surface.
- Step 3 � perform swap only in backed-up target directories; if vendor-level composer strategy is needed, apply to:
  - `libraries\vendor\autoload.php`
  - `libraries\vendor\composer\autoload_psr4.php`
  - `libraries\vendor\composer\installed.json`
  and keep resulting changes limited to SDK namespace/class map additions.
- Step 4 � verify restore markers:
  - plugin params still present (`wtotpravkapochtaru` row exists and keys unchanged)
  - `libraries/Webtolk/Otpravkapochtaru` files unchanged after revert.
  - `libraries/vendor` checksum stability for non-SDK entries.
- Step 5 � keep patch reversible by replacing from backup bundle on rollback.
