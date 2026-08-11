# worker artifact: t14-lapaygroup-core-swap-writer
run_id: lapaygroup-joomla-core-swap-20260811
task_id: t14-lapaygroup-core-swap-writer
mode: shell-worker
requested_model: gpt-5.3-codex-spark
completed_at: 2026-08-11

action: register local lapaygroup/russianpost SDK in Joomla stand vendor/classloader

## Backed up files (as required)
- Backup root: `D:\Dev\WT-Otpravkapochtaru-joomla-library\.pf\tmp\lapaygroup-core-swap-backup-20260811`
- `D:\OSPanel\home\joomla.local\public\libraries\vendor\composer\autoload_psr4.php`
- `D:\OSPanel\home\joomla.local\public\libraries\vendor\composer\autoload_static.php`

## Changed stand files
- Added SDK source: `D:\OSPanel\home\joomla.local\public\libraries\vendor\lapaygroup\russianpost\src` (36 files copied from `.pf\tmp\LapayGroup-RussianPost-2.0.0\RussianPost-2.0.0\src`)
- `D:\OSPanel\home\joomla.local\public\libraries\vendor\composer\autoload_psr4.php`
- `D:\OSPanel\home\joomla.local\public\libraries\vendor\composer\autoload_static.php`

## Exact classloader registration method
- Added PSR-4 mapping in `autoload_psr4.php`:
  - `'LapayGroup\\RussianPost\\' => array($vendorDir . '/lapaygroup/russianpost/src'),`
- Added matching Composer static map entries in `autoload_static.php`:
  - `public static $prefixLengthsPsr4['L']['LapayGroup\\RussianPost\\'] = 22;`
  - `public static $prefixDirsPsr4['LapayGroup\\RussianPost\\'][0] = __DIR__ . '/..' . '/lapaygroup/russianpost/src';`
- No changes to product repository source.

## Proof command and result
Command executed:
```
D:\OSPanel\modules\PHP-8.3\php.exe  D:\Dev\WT-Otpravkapochtaru-joomla-library\.pf\tmp\lapaygroup-core-swap-writer\autoload-proof.php
```
`autoload-proof.php` used:
```php
require 'D:/OSPanel/home/joomla.local/public/libraries/vendor/autoload.php';
$class = \LapayGroup\RussianPost\Http\Psr18Transport::class;
$requestFactory = new \Laminas\Diactoros\RequestFactory();
$streamFactory = new \Laminas\Diactoros\StreamFactory();
$uploadedFileFactory = new \Laminas\Diactoros\UploadedFileFactory();
$http = new \Joomla\Http\Http();
$transport = new \LapayGroup\RussianPost\Http\Psr18Transport($http, $requestFactory, $streamFactory, $uploadedFileFactory);
```
Output:
```
class_exists=yes
instanceof_transport=yes
http_client_interface=yes
request_factory_interface=yes
stream_factory_interface=yes
uploaded_factory_interface=yes
```
This proves `LapayGroup\RussianPost\Http\Psr18Transport` is autoloadable without manual `addPsr4` runtime calls and is instantiable with `Joomla\Http\Http` + Laminas Diactoros factories.

## Restore instructions
- Restore original autoloader files from backup:
  - `Copy-Item -Path "D:\Dev\WT-Otpravkapochtaru-joomla-library\.pf\tmp\lapaygroup-core-swap-backup-20260811\autoload_psr4.php" -Destination "D:\OSPanel\home\joomla.local\public\libraries\vendor\composer\autoload_psr4.php" -Force`
  - `Copy-Item -Path "D:\Dev\WT-Otpravkapochtaru-joomla-library\.pf\tmp\lapaygroup-core-swap-backup-20260811\autoload_static.php" -Destination "D:\OSPanel\home\joomla.local\public\libraries\vendor\composer\autoload_static.php" -Force`
- Remove copied SDK payload if needed:
  - `Remove-Item -Recurse -Force "D:\OSPanel\home\joomla.local\public\libraries\vendor\lapaygroup\russianpost"`

## Blockers / deviations
- No blockers.
- No Webtolk facade edits were required.
- No installed-webtolk product repo files were modified.
