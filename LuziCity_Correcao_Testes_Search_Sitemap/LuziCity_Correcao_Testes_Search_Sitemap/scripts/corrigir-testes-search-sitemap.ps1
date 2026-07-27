param(
    [string]$ProjectRoot = "D:\Skill\LuziCity"
)

$ErrorActionPreference = "Stop"

function Fail([string]$Message) {
    Write-Host ""
    Write-Host "ERRO: $Message" -ForegroundColor Red
    exit 1
}

function Patch-TestFile([string]$Path) {
    if (-not (Test-Path -LiteralPath $Path)) {
        Fail "Arquivo nao encontrado: $Path"
    }

    $content = [System.IO.File]::ReadAllText($Path)
    $original = $content

    if ($content -notmatch 'use\s+Illuminate\\Foundation\\Testing\\RefreshDatabase\s*;') {
        $namespacePattern = '(namespace\s+Tests\\[^;]+;\s*)'
        if ($content -notmatch $namespacePattern) {
            Fail "Namespace de teste nao localizado em: $Path"
        }
        $content = [regex]::Replace(
            $content,
            $namespacePattern,
            "`$1`r`nuse Illuminate\Foundation\Testing\RefreshDatabase;`r`n",
            1
        )
    }

    if ($content -notmatch 'use\s+RefreshDatabase\s*;') {
        $classPattern = '(class\s+\w+\s+extends\s+TestCase\s*\{\s*)'
        if ($content -notmatch $classPattern) {
            Fail "Declaracao da classe de teste nao localizada em: $Path"
        }
        $content = [regex]::Replace(
            $content,
            $classPattern,
            "`$1`r`n    use RefreshDatabase;`r`n",
            1
        )
    }

    if ($content -eq $original) {
        Write-Host "Ja corrigido: $Path" -ForegroundColor Yellow
        return
    }

    [System.IO.File]::WriteAllText(
        $Path,
        $content,
        [System.Text.UTF8Encoding]::new($false)
    )

    Write-Host "Corrigido: $Path" -ForegroundColor Green
}

if (-not (Test-Path -LiteralPath (Join-Path $ProjectRoot "artisan"))) {
    Fail "Projeto Laravel nao encontrado em $ProjectRoot"
}

$searchTest = Join-Path $ProjectRoot "tests\Feature\Search\UnifiedSearchTest.php"
$sitemapTest = Join-Path $ProjectRoot "tests\Feature\Seo\SitemapTest.php"

$stamp = Get-Date -Format "yyyyMMdd-HHmmss"
$backupDir = Join-Path $ProjectRoot "backups\antes-correcao-testes-search-sitemap-$stamp"
New-Item -ItemType Directory -Path $backupDir -Force | Out-Null

Copy-Item -LiteralPath $searchTest -Destination (Join-Path $backupDir "UnifiedSearchTest.php") -Force
Copy-Item -LiteralPath $sitemapTest -Destination (Join-Path $backupDir "SitemapTest.php") -Force

Write-Host "Backup criado em: $backupDir" -ForegroundColor Cyan

Patch-TestFile $searchTest
Patch-TestFile $sitemapTest

Push-Location $ProjectRoot
try {
    php artisan optimize:clear
    if ($LASTEXITCODE -ne 0) { Fail "Falha em php artisan optimize:clear" }

    php artisan test --filter=UnifiedSearchTest
    if ($LASTEXITCODE -ne 0) { Fail "UnifiedSearchTest ainda apresenta falha" }

    php artisan test --filter=SitemapTest
    if ($LASTEXITCODE -ne 0) { Fail "SitemapTest ainda apresenta falha" }

    php artisan test
    if ($LASTEXITCODE -ne 0) { Fail "A suite completa ainda apresenta falhas" }
}
finally {
    Pop-Location
}

Write-Host ""
Write-Host "CORRECAO CONCLUIDA COM SUCESSO." -ForegroundColor Green
Write-Host "Backup: $backupDir" -ForegroundColor Cyan
