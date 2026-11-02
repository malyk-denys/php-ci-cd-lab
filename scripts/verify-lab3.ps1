$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
$php = Join-Path $root '.tmp/runtime/php/php.exe'
if (-not (Test-Path $php)) { $php = (Get-Command php -ErrorAction Stop).Source }
Push-Location $root
try {
    foreach ($file in @('index.php') + @(Get-ChildItem app,tests -Recurse -Filter '*.php' | ForEach-Object FullName)) {
        & $php -l $file
        if ($LASTEXITCODE -ne 0) { throw "Syntax check failed: $file" }
    }
    & $php vendor/bin/pint --test
    if ($LASTEXITCODE -ne 0) { throw 'Pint failed' }
    & $php vendor/bin/phpunit tests/Unit
    if ($LASTEXITCODE -ne 0) { throw 'PHPUnit failed' }
    Write-Output 'LAB 3 VALIDATION: PASS'
} finally { Pop-Location }
