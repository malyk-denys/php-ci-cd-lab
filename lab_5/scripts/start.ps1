$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
$php = $env:LAB5_PHP
if (-not $php) { $php = Join-Path $root '.tmp/runtime/php/php.exe' }
if (-not (Test-Path $php)) { $php = (Get-Command php -ErrorAction Stop).Source }

Push-Location $root
try {
    Write-Output 'Lab 5: http://127.0.0.1:8000'
    & $php artisan serve --host=127.0.0.1 --port=8000
} finally {
    Pop-Location
}
