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
    $files = @('index.php') + @(Get-ChildItem app,tests -Recurse -Filter '*.php' | ForEach-Object FullName)
    foreach ($file in $files) {
        & $php -l $file
        if ($LASTEXITCODE -ne 0) { throw "Syntax check failed: $file" }
    }

    & $php vendor/bin/pint --test
    if ($LASTEXITCODE -ne 0) { throw 'Pint failed' }

    & $php vendor/bin/phpunit --colors=never
    if ($LASTEXITCODE -ne 0) { throw 'PHPUnit failed' }

    & $php index.php | Out-File -Encoding utf8 (Join-Path $root 'docs/browser-output.html')
    if ($LASTEXITCODE -ne 0) { throw 'index.php failed' }

    Write-Output 'LAB 4 VALIDATION: PASS'
} finally {
    Pop-Location
}
