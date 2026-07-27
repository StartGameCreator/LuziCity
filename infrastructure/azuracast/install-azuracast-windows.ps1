[CmdletBinding()]
param(
    [string] $ServiceRoot = 'D:\Skill\LuziCity-Services\AzuraCast'
)

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'common.ps1')

try {
    Write-AzuraCastLog 'Validando Docker Desktop e Docker Compose.'
    Assert-DockerReady

    $servicePath = Get-AzuraCastServiceRoot $ServiceRoot
    $stationPorts = foreach ($basePort in 9100..9190 | Where-Object { $_ % 10 -eq 0 }) {
        $basePort
        $basePort + 5
        $basePort + 6
    }
    if (-not (Test-Path -LiteralPath (Join-Path $servicePath 'docker-compose.yml'))) {
        Assert-PortsAvailable -Ports (@(8080, 8443, 2022) + $stationPorts)
    }

    $binPath = Join-Path $servicePath 'bin'
    New-Item -ItemType Directory -Path $binPath -Force | Out-Null

    $wslRoot = ConvertTo-WslPath $servicePath
    Write-AzuraCastLog "Preparando instalacao externa em $servicePath."

    $dockerWrapper = "#!/usr/bin/env bash`nexec `"/mnt/c/Program Files/Docker/Docker/resources/bin/docker.exe`" `"`$@`"`n"
    [System.IO.File]::WriteAllText(
        (Join-Path $binPath 'docker'),
        $dockerWrapper,
        [System.Text.UTF8Encoding]::new($false)
    )

    $bootstrap = @"
set -euo pipefail
cd $(ConvertTo-BashLiteral $wslRoot)
mkdir -p bin backups
chmod +x bin/docker
curl -fsSL https://raw.githubusercontent.com/AzuraCast/AzuraCast/main/docker.sh -o docker.sh
chmod +x docker.sh
curl -fsSL https://raw.githubusercontent.com/AzuraCast/AzuraCast/stable/sample.env -o .env.download
if [ ! -s .env ]; then mv .env.download .env; else rm .env.download; fi
curl -fsSL https://raw.githubusercontent.com/AzuraCast/AzuraCast/stable/azuracast.sample.env -o azuracast.env.download
if [ ! -s azuracast.env ]; then mv azuracast.env.download azuracast.env; else rm azuracast.env.download; fi
"@
    & wsl.exe -d Ubuntu -u root --exec bash -lc $bootstrap
    if ($LASTEXITCODE -ne 0) {
        throw "Falha ao preparar o instalador oficial (codigo $LASTEXITCODE)."
    }

    $composeEnv = Join-Path $servicePath '.env'
    Set-DotEnvValue -Path $composeEnv -Key 'AZURACAST_VERSION' -Value 'stable'
    Set-DotEnvValue -Path $composeEnv -Key 'AZURACAST_HTTP_PORT' -Value '8080'
    Set-DotEnvValue -Path $composeEnv -Key 'AZURACAST_HTTPS_PORT' -Value '8443'
    Set-DotEnvValue -Path $composeEnv -Key 'AZURACAST_SFTP_PORT' -Value '2022'
    Set-DotEnvValue -Path $composeEnv -Key 'AZURACAST_STATION_PORTS' -Value ($stationPorts -join ',')

    $azuraCastEnv = Join-Path $servicePath 'azuracast.env'
    Set-DotEnvValue -Path $azuraCastEnv -Key 'AUTO_ASSIGN_PORT_MIN' -Value '9100'
    Set-DotEnvValue -Path $azuraCastEnv -Key 'AUTO_ASSIGN_PORT_MAX' -Value '9199'

    Write-AzuraCastLog 'Baixando imagens e executando o instalador oficial no canal stable.'
    Invoke-AzuraCastBash -ServiceRoot $servicePath -Command "yes '' | ./docker.sh install 2>&1 | tee -a $(ConvertTo-BashLiteral (ConvertTo-WslPath (Get-AzuraCastLogPath)))"

    Write-AzuraCastLog 'AzuraCast instalado e iniciado.' 'Green'
    Write-Host ''
    Write-Host 'URL:   http://127.0.0.1:8080' -ForegroundColor Green
    Write-Host 'HTTPS: https://127.0.0.1:8443' -ForegroundColor Green
    Write-Host 'SFTP:  127.0.0.1:2022' -ForegroundColor Green
    Write-Host 'Proximo passo: abra a URL, crie o Super Administrador e gere uma API Key.' -ForegroundColor Yellow
    & (Join-Path $PSScriptRoot 'status-azuracast.ps1') -ServiceRoot $servicePath
} catch {
    Write-AzuraCastLog $_.Exception.Message 'Red'
    throw
}
