[CmdletBinding()]
param([string] $ServiceRoot = 'D:\Skill\LuziCity-Services\AzuraCast')

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'common.ps1')

Assert-DockerReady
$servicePath = Get-AzuraCastServiceRoot $ServiceRoot

Write-AzuraCastLog 'Criando backup obrigatorio antes da atualizacao.'
& (Join-Path $PSScriptRoot 'backup-azuracast.ps1') -ServiceRoot $servicePath

Write-AzuraCastLog 'Atualizando utilitario oficial e imagens do AzuraCast.'
Invoke-AzuraCastBash -ServiceRoot $servicePath -Command './docker.sh update-self'
Invoke-AzuraCastBash -ServiceRoot $servicePath -Command "yes '' | ./docker.sh update"
Write-AzuraCastLog 'Atualizacao concluida sem remover volumes.' 'Green'
