[CmdletBinding()]
param()

$ErrorActionPreference = 'Stop'
$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
$logPath = Join-Path $projectRoot 'storage\logs\docker-install.log'
$installerPath = Join-Path $env:TEMP 'DockerDesktopInstaller-full.exe'
$wslInstallerPath = Join-Path $env:TEMP 'wsl.2.9.3.0.x64.msi'

function Write-Step {
    param([string] $Message)

    $line = "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] $Message"
    Write-Host $line -ForegroundColor Cyan
    Add-Content -LiteralPath $logPath -Value $line
}

function Test-Administrator {
    $identity = [Security.Principal.WindowsIdentity]::GetCurrent()
    $principal = [Security.Principal.WindowsPrincipal]::new($identity)

    return $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
}

if (-not (Test-Administrator)) {
    Write-Host 'Solicitando permissao administrativa...' -ForegroundColor Yellow
    Start-Process -FilePath 'powershell.exe' -Verb RunAs -ArgumentList @(
        '-NoExit',
        '-NoProfile',
        '-ExecutionPolicy',
        'Bypass',
        '-File',
        "`"$PSCommandPath`""
    )
    exit
}

New-Item -ItemType Directory -Path (Split-Path $logPath) -Force | Out-Null
Write-Step 'Habilitando Windows Subsystem for Linux.'
Enable-WindowsOptionalFeature -Online -FeatureName Microsoft-Windows-Subsystem-Linux -All -NoRestart | Out-Null
Write-Step 'Habilitando Virtual Machine Platform.'
Enable-WindowsOptionalFeature -Online -FeatureName VirtualMachinePlatform -All -NoRestart | Out-Null

& wsl.exe --version *> $null
if ($LASTEXITCODE -ne 0) {
    Write-Step 'Instalando pacote oficial Microsoft WSL 2.9.3.'
    & curl.exe `
        --location `
        --fail `
        --show-error `
        --retry 12 `
        --retry-all-errors `
        --retry-delay 5 `
        --continue-at - `
        --output $wslInstallerPath `
        'https://github.com/microsoft/WSL/releases/download/2.9.3/wsl.2.9.3.0.x64.msi'
    if ($LASTEXITCODE -ne 0) {
        throw "Falha ao baixar o pacote WSL (curl: $LASTEXITCODE)."
    }

    $wslSignature = Get-AuthenticodeSignature -FilePath $wslInstallerPath
    if ($wslSignature.Status -ne 'Valid' -or $wslSignature.SignerCertificate.Subject -notmatch 'Microsoft') {
        throw "Assinatura digital do WSL invalida: $($wslSignature.Status)"
    }

    $wslInstaller = Start-Process -FilePath 'msiexec.exe' -Wait -PassThru -ArgumentList @(
        '/i',
        $wslInstallerPath,
        '/qn',
        '/norestart'
    )
    if ($wslInstaller.ExitCode -notin @(0, 3010)) {
        throw "O instalador WSL retornou o codigo $($wslInstaller.ExitCode)."
    }
    Write-Step "Pacote WSL instalado (codigo $($wslInstaller.ExitCode))."
}

if (-not (Test-Path 'C:\Program Files\Docker\Docker\Docker Desktop.exe')) {
    Write-Step 'Baixando Docker Desktop do dominio oficial.'
    & curl.exe `
        --location `
        --fail `
        --show-error `
        --retry 12 `
        --retry-all-errors `
        --retry-delay 5 `
        --continue-at - `
        --output $installerPath `
        'https://desktop.docker.com/win/main/amd64/Docker%20Desktop%20Installer.exe'
    if ($LASTEXITCODE -ne 0) {
        throw "Falha ao baixar Docker Desktop (curl: $LASTEXITCODE)."
    }

    Write-Step 'Validando assinatura digital do instalador.'
    $signature = Get-AuthenticodeSignature -FilePath $installerPath
    if ($signature.Status -ne 'Valid' -or $signature.SignerCertificate.Subject -notmatch 'Docker') {
        throw "Assinatura digital invalida: $($signature.Status)"
    }

    Write-Step 'Instalando Docker Desktop com backend WSL2.'
    $installer = Start-Process -FilePath $installerPath -Wait -PassThru -ArgumentList @(
        'install',
        '--quiet',
        '--accept-license',
        '--backend=wsl-2'
    )

    if ($installer.ExitCode -notin @(0, 3010)) {
        throw "O instalador retornou o codigo $($installer.ExitCode)."
    }
} else {
    Write-Step 'Docker Desktop ja esta instalado.'
}

Write-Step 'Instalacao concluida. Reinicie o Windows antes de iniciar o Docker Desktop.'
Write-Host ''
Write-Host 'Depois da reinicializacao, retorne ao Codex para concluir o AzuraCast.' -ForegroundColor Green
