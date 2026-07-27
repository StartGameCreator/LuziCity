[CmdletBinding()]
param(
    [string] $ServiceRoot = 'D:\Skill\LuziCity-Services\AzuraCast',
    [string] $Destination
)

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'common.ps1')

Assert-DockerReady
$servicePath = Get-AzuraCastServiceRoot $ServiceRoot
if (-not $Destination) {
    $Destination = Join-Path $servicePath ('backups\azuracast-{0}.tar.gz' -f (Get-Date -Format 'yyyyMMdd-HHmmss'))
}

$destinationPath = [System.IO.Path]::GetFullPath($Destination)
$destinationDirectory = Split-Path $destinationPath
New-Item -ItemType Directory -Path $destinationDirectory -Force | Out-Null

$wslDestination = ConvertTo-WslPath $destinationPath
Invoke-AzuraCastBash -ServiceRoot $servicePath -Command "./docker.sh backup $(ConvertTo-BashLiteral $wslDestination)"
Write-Host "Backup criado em $destinationPath" -ForegroundColor Green
