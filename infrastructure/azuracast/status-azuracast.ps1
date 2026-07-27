[CmdletBinding()]
param([string] $ServiceRoot = 'D:\Skill\LuziCity-Services\AzuraCast')

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'common.ps1')

Assert-DockerReady
Invoke-AzuraCastBash -ServiceRoot $ServiceRoot -Command "docker compose ps && printf '\nPortas configuradas:\n' && grep -E '^AZURACAST_(HTTP|HTTPS|SFTP)_PORT=' .env"
