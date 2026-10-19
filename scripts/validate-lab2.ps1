$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
$docs = Join-Path $root 'docs'
$required = @(
    'architecture.md',
    'use-case-diagram.puml',
    'use-case-diagram.svg',
    'use-case-diagram.png',
    'class-diagram.puml',
    'class-diagram.svg',
    'class-diagram.png',
    'Lab2_Report.docx'
)

foreach ($file in $required) {
    $path = Join-Path $docs $file
    if (-not (Test-Path -LiteralPath $path)) {
        throw "Missing required artifact: $file"
    }
    if ((Get-Item -LiteralPath $path).Length -eq 0) {
        throw "Artifact is empty: $file"
    }
}

$useCase = Get-Content -Raw -LiteralPath (Join-Path $docs 'use-case-diagram.puml')
$actorCount = ([regex]::Matches($useCase, '(?m)^actor ')).Count
if ($actorCount -lt 3) { throw "Use Case Diagram has $actorCount actors; expected at least 3" }
foreach ($token in @('<<include>>', '<<extend>>', '--|>')) {
    if (-not $useCase.Contains($token)) { throw "Use Case Diagram is missing $token" }
}

$classDiagram = Get-Content -Raw -LiteralPath (Join-Path $docs 'class-diagram.puml')
$classMatches = [regex]::Matches($classDiagram, '(?ms)^\s*(?:abstract\s+)?class\s+(\w+)\s*\{(.*?)^\s*\}')
if ($classMatches.Count -lt 10) {
    throw "Class Diagram has $($classMatches.Count) classes; expected at least 10"
}

foreach ($match in $classMatches) {
    $name = $match.Groups[1].Value
    $body = $match.Groups[2].Value
    $attributes = ([regex]::Matches($body, '(?m)^\s*-\s+')).Count
    $operations = ([regex]::Matches($body, '(?m)^\s*\+\s+')).Count
    if ($attributes -lt 5 -or $operations -lt 5) {
        throw "Class $name has $attributes attributes and $operations operations; expected at least 5 of each"
    }
}

foreach ($token in @(' o-- ', ' *-- ', ' <|-- ', ' -- ')) {
    if (-not $classDiagram.Contains($token)) { throw "Class Diagram is missing relationship token: $token" }
}

$architecture = Get-Content -Raw -LiteralPath (Join-Path $docs 'architecture.md')
$scenarioSection = [regex]::Match(
    $architecture,
    '(?ms)#### Головний успішний сценарій\s+(.*?)\s+#### Розширення'
).Groups[1].Value
$stepCount = ([regex]::Matches($scenarioSection, '(?m)^\d+\. ')).Count
if ($stepCount -lt 10) { throw "Full Use Case has $stepCount steps; expected at least 10" }

$extensionSection = [regex]::Match(
    $architecture,
    '(?ms)#### Розширення та альтернативні сценарії\s+(.*?)\s+## 9 '
).Groups[1].Value
$extensionCount = ([regex]::Matches($extensionSection, '(?m)^\d+\. ')).Count
if ($extensionCount -lt 5) { throw "Full Use Case has $extensionCount alternatives; expected at least 5" }

Write-Host "Actors: $actorCount"
Write-Host "Classes: $($classMatches.Count)"
Write-Host "Full Use Case steps: $stepCount"
Write-Host "Alternative scenarios: $extensionCount"
Write-Host 'LAB_2 VALIDATION: PASS'
