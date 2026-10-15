[CmdletBinding()]
param()

$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot

$requiredFiles = @(
    '.gitattributes',
    '.gitignore',
    'composer.json',
    'phpunit.xml',
    'README.md',
    'VALIDATION.md',
    '.github/PULL_REQUEST_TEMPLATE.md',
    '.github/workflows/php-ci.yml',
    'app/Services/Calculator.php',
    'tests/Unit/CalculatorTest.php',
    'docs/INITIAL_FAILURE.md',
    'docs/GITHUB_CHECKLIST.md'
)

$failures = [System.Collections.Generic.List[string]]::new()

foreach ($relativePath in $requiredFiles) {
    $fullPath = Join-Path $projectRoot $relativePath
    if (-not (Test-Path -LiteralPath $fullPath -PathType Leaf)) {
        $failures.Add("Missing required file: $relativePath")
    }
}

try {
    $composer = Get-Content -Raw -LiteralPath (Join-Path $projectRoot 'composer.json') | ConvertFrom-Json
    if ($composer.name -ne 'student/ci-cd-lab') {
        $failures.Add('composer.json has an unexpected package name.')
    }
    if ($composer.autoload.'psr-4'.'App\' -ne 'app/') {
        $failures.Add('PSR-4 mapping for App\ is missing or invalid.')
    }
    if ($composer.'autoload-dev'.'psr-4'.'Tests\' -ne 'tests/') {
        $failures.Add('PSR-4 mapping for Tests\ is missing or invalid.')
    }
    if (-not $composer.'require-dev'.'phpunit/phpunit' -or -not $composer.'require-dev'.'laravel/pint') {
        $failures.Add('PHPUnit or Pint is missing from require-dev.')
    }
}
catch {
    $failures.Add("composer.json is not valid JSON: $($_.Exception.Message)")
}

$workflow = Get-Content -Raw -LiteralPath (Join-Path $projectRoot '.github/workflows/php-ci.yml')
$requiredWorkflowFragments = @(
    'actions/checkout@v4',
    'shivammathur/setup-php@v2',
    'php-version: "8.2"',
    './vendor/bin/pint --test',
    './vendor/bin/phpunit tests/Unit',
    '${{ secrets.MY_SUPER_SECRET_KEY }}',
    'Demonstrate GitHub Secrets'
)

foreach ($fragment in $requiredWorkflowFragments) {
    if (-not $workflow.Contains($fragment)) {
        $failures.Add("Workflow fragment is missing: $fragment")
    }
}

$calculator = Get-Content -Raw -LiteralPath (Join-Path $projectRoot 'app/Services/Calculator.php')
if ($calculator -notmatch 'return \$x \+ \$y;' -or $calculator -notmatch 'return \$x - \$y;') {
    $failures.Add('Calculator does not contain the required formatted add/subtract operations.')
}

$tests = Get-Content -Raw -LiteralPath (Join-Path $projectRoot 'tests/Unit/CalculatorTest.php')
if ($tests -notmatch 'add\(2, 3\)' -or $tests -notmatch 'subtract\(5, 2\)') {
    $failures.Add('Calculator tests do not cover the required examples.')
}

$phpCommand = Get-Command php -ErrorAction SilentlyContinue
if ($phpCommand) {
    foreach ($relativePath in @('app/Services/Calculator.php', 'tests/Unit/CalculatorTest.php')) {
        $fullPath = Join-Path $projectRoot $relativePath
        & $phpCommand.Source -l $fullPath
        if ($LASTEXITCODE -ne 0) {
            $failures.Add("PHP syntax check failed: $relativePath")
        }
    }
}
else {
    Write-Host '[SKIP] PHP syntax check: php is not installed locally; GitHub Actions performs it through PHPUnit.'
}

$composerCommand = Get-Command composer -ErrorAction SilentlyContinue
if ($composerCommand) {
    Push-Location $projectRoot
    try {
        & $composerCommand.Source validate --strict --no-check-publish
        if ($LASTEXITCODE -ne 0) {
            $failures.Add('Composer validation failed.')
        }
    }
    finally {
        Pop-Location
    }
}
else {
    Write-Host '[SKIP] Composer validation: composer is not installed locally; JSON and required fields were checked.'
}

if ($failures.Count -gt 0) {
    Write-Host 'LAB_1 VALIDATION: FAIL' -ForegroundColor Red
    $failures | ForEach-Object { Write-Host " - $_" -ForegroundColor Red }
    exit 1
}

Write-Host 'LAB_1 VALIDATION: PASS' -ForegroundColor Green
Write-Host "Checked $($requiredFiles.Count) required files, Composer metadata, CI workflow, Calculator code, and unit-test cases."

