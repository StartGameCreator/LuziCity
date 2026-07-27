[CmdletBinding()]
param([string] $ServiceRoot = 'D:\Skill\LuziCity-Services\AzuraCast')

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'common.ps1')

Assert-DockerReady
Invoke-AzuraCastBash -ServiceRoot $ServiceRoot -Command 'docker compose stop'
Write-Host 'AzuraCast parado. Os volumes e dados foram preservados.' -ForegroundColor Yellow
