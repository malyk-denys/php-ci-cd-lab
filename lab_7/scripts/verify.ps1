$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
$php = $env:LAB7_PHP
if (-not $php) { $php = Join-Path $root '.tmp/runtime/php/php.exe' }
if (-not (Test-Path $php)) { $php = (Get-Command php -ErrorAction Stop).Source }

Push-Location $root
try {
    $environment = Join-Path $root '.env'
    if (-not (Test-Path $environment)) {
        Copy-Item (Join-Path $root '.env.example') $environment
    }

    $database = Join-Path $root 'database/database.sqlite'
    if (-not (Test-Path $database)) {
        New-Item -ItemType File -Path $database | Out-Null
    }

    & $php artisan key:generate --force
    if ($LASTEXITCODE -ne 0) { throw 'Application key generation failed' }

    $phpFiles = @(
        Get-ChildItem app,bootstrap,config,database,routes,tests -Recurse -Filter '*.php' |
            ForEach-Object FullName
    )

    foreach ($file in $phpFiles) {
        & $php -l $file | Out-Null
        if ($LASTEXITCODE -ne 0) { throw "Syntax check failed: $file" }
    }

    & $php artisan migrate:fresh --seed --force
    if ($LASTEXITCODE -ne 0) { throw 'Migration and seeding failed' }

    & $php artisan view:clear
    & $php artisan view:cache
    if ($LASTEXITCODE -ne 0) { throw 'Blade compilation failed' }

    & $php artisan route:list --except-vendor
    if ($LASTEXITCODE -ne 0) { throw 'Route inspection failed' }

    & $php vendor/bin/pint --test
    if ($LASTEXITCODE -ne 0) { throw 'Pint failed' }

    & $php artisan test
    if ($LASTEXITCODE -ne 0) { throw 'Laravel tests failed' }

    Write-Output 'LAB 7 VALIDATION: PASS'
} finally {
    Pop-Location
}
