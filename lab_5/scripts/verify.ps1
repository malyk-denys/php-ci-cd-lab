$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
$php = $env:LAB5_PHP
if (-not $php) { $php = Join-Path $root '.tmp/runtime/php/php.exe' }
if (-not (Test-Path $php)) { $php = (Get-Command php -ErrorAction Stop).Source }

Push-Location $root
try {
    $phpFiles = @(
        Get-ChildItem app,bootstrap,config,database,routes,tests -Recurse -Filter '*.php' |
            ForEach-Object FullName
    )

    foreach ($file in $phpFiles) {
        & $php -l $file | Out-Null
        if ($LASTEXITCODE -ne 0) { throw "Syntax check failed: $file" }
    }

    & $php artisan route:list
    if ($LASTEXITCODE -ne 0) { throw 'Route inspection failed' }

    & $php vendor/bin/pint --test
    if ($LASTEXITCODE -ne 0) { throw 'Pint failed' }

    & $php artisan test
    if ($LASTEXITCODE -ne 0) { throw 'Laravel tests failed' }

    Write-Output 'LAB 5 VALIDATION: PASS'
} finally {
    Pop-Location
}
