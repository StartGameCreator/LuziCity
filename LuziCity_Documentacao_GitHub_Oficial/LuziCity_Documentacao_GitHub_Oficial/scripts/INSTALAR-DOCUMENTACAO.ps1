param([string]$ProjectRoot = "D:\Skill\LuziCity")
$ErrorActionPreference = "Stop"
if (-not (Test-Path (Join-Path $ProjectRoot "artisan"))) { throw "Raiz do LuziCity invalida: $ProjectRoot" }
$source = Split-Path -Parent $PSScriptRoot
$stamp = Get-Date -Format "yyyyMMdd-HHmmss"
$backup = Join-Path $ProjectRoot "backups\antes-documentacao-$stamp"
New-Item -ItemType Directory -Force -Path $backup | Out-Null
foreach ($name in @("README.md","CONTRIBUTING.md","CHANGELOG.md","docs")) {
  $target = Join-Path $ProjectRoot $name
  if (Test-Path $target) { Copy-Item $target -Destination $backup -Recurse -Force }
}
Copy-Item (Join-Path $source "README.md") (Join-Path $ProjectRoot "README.md") -Force
Copy-Item (Join-Path $source "CONTRIBUTING.md") (Join-Path $ProjectRoot "CONTRIBUTING.md") -Force
Copy-Item (Join-Path $source "CHANGELOG.md") (Join-Path $ProjectRoot "CHANGELOG.md") -Force
Copy-Item (Join-Path $source "docs\*") (Join-Path $ProjectRoot "docs") -Recurse -Force
Write-Host "Documentacao instalada. Backup: $backup"
