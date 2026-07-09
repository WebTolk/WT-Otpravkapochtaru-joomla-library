$ErrorActionPreference = 'Stop'

$paths = @(
    'lib_webtolk_otpravkapochtaru',
    'plg_system_wt_otpravkapochtaru',
    'tests'
)

$files = foreach ($path in $paths) {
    if (Test-Path -LiteralPath $path) {
        Get-ChildItem -LiteralPath $path -Recurse -File -Filter '*.php'
    }
}

foreach ($file in $files) {
    php -l $file.FullName

    if ($LASTEXITCODE -ne 0) {
        exit $LASTEXITCODE
    }
}
