[CmdletBinding()]
param()

$ErrorActionPreference = 'Stop'

function Get-LuziCityProjectRoot {
    return (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
}

function Get-AzuraCastServiceRoot {
    param([string] $ServiceRoot = 'D:\Skill\LuziCity-Services\AzuraCast')

    return [System.IO.Path]::GetFullPath($ServiceRoot)
}

function Get-AzuraCastLogPath {
    $logDirectory = Join-Path (Get-LuziCityProjectRoot) 'storage\logs'
    New-Item -ItemType Directory -Path $logDirectory -Force | Out-Null

    return Join-Path $logDirectory 'azuracast-install.log'
}

function Write-AzuraCastLog {
    param(
        [Parameter(Mandatory)]
        [string] $Message,
        [ValidateSet('Cyan', 'Green', 'Yellow', 'Red')]
        [string] $Color = 'Cyan'
    )

    $line = "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] $Message"
    Write-Host $line -ForegroundColor $Color
    Add-Content -LiteralPath (Get-AzuraCastLogPath) -Value $line
}

function Assert-DockerReady {
    if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
        throw 'Docker CLI nao encontrado. Instale o Docker Desktop antes de continuar.'
    }

    & docker info *> $null
    if ($LASTEXITCODE -ne 0) {
        throw 'O daemon Docker nao esta ativo. Inicie o Docker Desktop e tente novamente.'
    }

    & docker compose version *> $null
    if ($LASTEXITCODE -ne 0) {
        throw 'Docker Compose v2 nao esta disponivel.'
    }
}

function ConvertTo-WslPath {
    param([Parameter(Mandatory)][string] $WindowsPath)

    $fullPath = [System.IO.Path]::GetFullPath($WindowsPath)
    if ($fullPath -notmatch '^([A-Za-z]):\\(.*)$') {
        throw "Caminho Windows invalido para o WSL: $fullPath"
    }

    $drive = $Matches[1].ToLowerInvariant()
    $tail = $Matches[2] -replace '\\', '/'

    return "/mnt/$drive/$tail"
}

function ConvertTo-BashLiteral {
    param([Parameter(Mandatory)][string] $Value)

    return "'" + ($Value -replace "'", "'\''") + "'"
}

function Invoke-AzuraCastBash {
    param(
        [Parameter(Mandatory)]
        [string] $Command,
        [string] $ServiceRoot = 'D:\Skill\LuziCity-Services\AzuraCast'
    )

    $wslRoot = ConvertTo-WslPath (Get-AzuraCastServiceRoot $ServiceRoot)
    $shellCommand = "set -euo pipefail; export PATH=$(ConvertTo-BashLiteral "$wslRoot/bin"):`$PATH; cd $(ConvertTo-BashLiteral $wslRoot); $Command"

    # O disco D: aparece como propriedade de root no WSL/9p. Executar como root
    # permite ao instalador oficial preservar timestamps e permissões dos .env.
    & wsl.exe -d Ubuntu -u root --exec bash -lc $shellCommand
    if ($LASTEXITCODE -ne 0) {
        throw "Comando AzuraCast falhou com codigo $LASTEXITCODE."
    }
}

function Assert-PortsAvailable {
    param([int[]] $Ports = @(8080, 8443, 2022))

    $netstat = & netstat.exe -ano -p tcp
    foreach ($port in $Ports) {
        if ($netstat | Where-Object { $_ -match "[:.]$port\s+.*LISTENING\s+\d+\s*$" }) {
            throw "A porta TCP $port ja esta em uso."
        }
    }
}

function Set-DotEnvValue {
    param(
        [Parameter(Mandatory)][string] $Path,
        [Parameter(Mandatory)][string] $Key,
        [Parameter(Mandatory)][string] $Value
    )

    $lines = [System.Collections.Generic.List[string]]::new()
    if (Test-Path -LiteralPath $Path) {
        foreach ($line in @(Get-Content -LiteralPath $Path)) {
            $lines.Add($line)
        }
    }

    $replacement = "$Key=$Value"
    $found = $false
    for ($index = 0; $index -lt $lines.Count; $index++) {
        if ($lines[$index] -match "^\s*$([regex]::Escape($Key))=") {
            $lines[$index] = $replacement
            $found = $true
        }
    }

    if (-not $found) {
        $lines.Add($replacement)
    }

    $utf8WithoutBom = [System.Text.UTF8Encoding]::new($false)
    [System.IO.File]::WriteAllLines($Path, $lines, $utf8WithoutBom)
}
