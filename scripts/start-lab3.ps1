param([int]$Port = 8000)
$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
$php = Join-Path $root '.tmp/runtime/php/php.exe'
if (-not (Test-Path $php)) { $php = (Get-Command php -ErrorAction Stop).Source }
if (-not (Test-Path (Join-Path $root 'vendor/autoload.php'))) {
    throw 'Спочатку виконайте composer install у корені проєкту.'
}
Push-Location $root
try { & $php -S "localhost:$Port" -t $root } finally { Pop-Location }
