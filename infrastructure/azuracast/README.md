# AzuraCast local do LuziCity

Esta infraestrutura instala o AzuraCast no mesmo computador do LuziCity, mas
isolado em containers e com dados fora do repositório Laravel.

## Endereços

- LuziCity: `http://127.0.0.1:9001`
- AzuraCast HTTP: `http://127.0.0.1:8080`
- AzuraCast HTTPS local: `https://127.0.0.1:8443`
- AzuraCast SFTP: `127.0.0.1:2022`
- Portas das emissoras: `9100–9199`
- Dados e configuração: `D:\Skill\LuziCity-Services\AzuraCast`
- Log do instalador: `storage\logs\azuracast-install.log`

## Instalação

Abra o PowerShell com o Docker Desktop em execução:

```powershell
.\infrastructure\azuracast\install-azuracast-windows.ps1
```

O script usa o instalador oficial, fixa o canal `stable`, preserva arquivos
existentes e interrompe imediatamente se Docker, Compose ou portas não estiverem
disponíveis.

## Operação

```powershell
.\infrastructure\azuracast\start-azuracast.ps1
.\infrastructure\azuracast\stop-azuracast.ps1
.\infrastructure\azuracast\status-azuracast.ps1
.\infrastructure\azuracast\backup-azuracast.ps1
.\infrastructure\azuracast\update-azuracast.ps1
```

O comando de parada não remove containers, volumes nem dados. A atualização
sempre cria um backup antes e não executa `down -v`, `prune --volumes` ou
`uninstall`.

## Configuração do Laravel

Após criar o Super Administrador e uma API Key no AzuraCast, configure o `.env`
local do LuziCity. Nunca publique a chave:

```dotenv
AZURACAST_ENABLED=true
AZURACAST_BASE_URL=http://127.0.0.1:8080
AZURACAST_API_KEY=
AZURACAST_STATION_ID=
AZURACAST_STATION_SHORTCODE=
AZURACAST_TIMEOUT=10
AZURACAST_VERIFY_SSL=false
AZURACAST_CACHE_SECONDS=10
```
