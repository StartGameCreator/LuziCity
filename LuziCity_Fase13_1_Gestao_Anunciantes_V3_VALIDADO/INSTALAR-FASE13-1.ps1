$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest

$Project = 'D:\Skill\LuziCity'
$Patch = Split-Path -Parent $MyInvocation.MyCommand.Path
if (Test-Path (Join-Path $Patch 'artisan')) { $Project = $Patch }
if (-not (Test-Path (Join-Path $Project 'artisan'))) { throw "Projeto Laravel nao encontrado em $Project" }

$Stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$Backup = Join-Path $Project "backups\antes-fase13-1-$Stamp"
New-Item -ItemType Directory -Force -Path $Backup | Out-Null
$Log = Join-Path $Patch "LOG_INSTALACAO_FASE13_1_$Stamp.txt"
Start-Transcript -Path $Log -Force | Out-Null

function Backup-RelativeFile([string]$Relative) {
    $Source = Join-Path $Project $Relative
    if (Test-Path $Source) {
        $Destination = Join-Path $Backup $Relative
        New-Item -ItemType Directory -Force -Path (Split-Path -Parent $Destination) | Out-Null
        Copy-Item -LiteralPath $Source -Destination $Destination -Force
    }
}

function Copy-PatchTree([string]$Relative) {
    $Source = Join-Path $Patch $Relative
    $Destination = Join-Path $Project $Relative
    if (Test-Path $Source) {
        New-Item -ItemType Directory -Force -Path $Destination | Out-Null
        Copy-Item -Path (Join-Path $Source '*') -Destination $Destination -Recurse -Force
    }
}

try {
    Write-Host '=================================================='
    Write-Host ' LUZICITY - INSTALACAO FASE 13.1'
    Write-Host '=================================================='
    Write-Host "Projeto: $Project"
    Write-Host "Patch:   $Patch"
    Write-Host "Backup:  $Backup"

    @(
      'routes\web.php',
      'app\Http\Controllers\AdminDashboardController.php',
      'resources\views\layouts\app.blade.php',
      'app\Models\AdvertiserProfile.php'
    ) | ForEach-Object { Backup-RelativeFile $_ }

    $Db = Join-Path $Project 'database\database.sqlite'
    if (Test-Path $Db) { Copy-Item $Db (Join-Path $Backup 'database.sqlite') -Force }

    Copy-PatchTree 'app'
    Copy-PatchTree 'database'
    Copy-PatchTree 'resources'
    Copy-PatchTree 'routes'

    Push-Location $Project
    try {
        & php (Join-Path $Patch 'scripts\aplicar-integracoes.php')
        if ($LASTEXITCODE -ne 0) { throw 'Falha ao aplicar integracoes.' }

        Get-ChildItem (Join-Path $Patch 'app'),(Join-Path $Patch 'routes'),(Join-Path $Patch 'database\migrations') -Recurse -Filter *.php | ForEach-Object {
            & php -l $_.FullName | Out-Host
            if ($LASTEXITCODE -ne 0) { throw "Sintaxe PHP invalida: $($_.FullName)" }
        }

        & php artisan optimize:clear
        if ($LASTEXITCODE -ne 0) { throw 'Falha em optimize:clear.' }
        & php artisan migrate --force
        if ($LASTEXITCODE -ne 0) { throw 'Falha nas migrations.' }
        & php artisan route:list --name=admin.advertisers
        if ($LASTEXITCODE -ne 0) { throw 'Rotas de anunciantes nao foram registradas.' }
        & php artisan view:cache
        if ($LASTEXITCODE -ne 0) { throw 'Falha ao compilar views.' }
        & php artisan optimize:clear
        if ($LASTEXITCODE -ne 0) { throw 'Falha na limpeza final.' }
    } finally { Pop-Location }

    Set-Content -Path (Join-Path $Backup 'CAMINHO_BACKUP.txt') -Value $Backup -Encoding UTF8
    Write-Host ''
    Write-Host 'FASE 13.1 INSTALADA COM SUCESSO.' -ForegroundColor Green
    Write-Host 'Acesso: http://127.0.0.1:9001/admin/comercial/anunciantes'
    Write-Host "Log: $Log"
    Stop-Transcript | Out-Null
    exit 0
} catch {
    Write-Host ''
    Write-Host "ERRO: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "Backup preservado em: $Backup"
    Write-Host "Log: $Log"
    try { Stop-Transcript | Out-Null } catch {}
    exit 1
}
