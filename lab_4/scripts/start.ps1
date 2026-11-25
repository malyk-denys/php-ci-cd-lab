$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
$phpCandidates = @(
    (Join-Path $root '.tmp/runtime/php/php.exe'),
    (Join-Path $root '..\.tmp\runtime\php\php.exe')
)
$php = $phpCandidates | Where-Object { Test-Path -LiteralPath $_ } | Select-Object -First 1
if (-not $php) { $php = (Get-Command php -ErrorAction Stop).Source }

Push-Location $root
try {
    & $php -S 127.0.0.1:8000 index.php
} finally {
    Pop-Location
}
