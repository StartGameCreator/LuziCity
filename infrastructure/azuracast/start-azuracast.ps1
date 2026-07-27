[CmdletBinding()]
param([string] $ServiceRoot = 'D:\Skill\LuziCity-Services\AzuraCast')

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'common.ps1')

Assert-DockerReady
Invoke-AzuraCastBash -ServiceRoot $ServiceRoot -Command 'docker compose up -d'
Write-Host 'AzuraCast iniciado em http://127.0.0.1:8080' -ForegroundColor Green
