@echo off
setlocal EnableExtensions EnableDelayedExpansion
title LuziCity - Inicializador Completo
color 0B

set "PROJECT_ROOT=%~dp0"
set "LOG_DIR=%PROJECT_ROOT%storage\logs"
set "AZURACAST_SCRIPT=%PROJECT_ROOT%infrastructure\azuracast\start-azuracast.ps1"
set "DOCKER_DESKTOP=C:\Program Files\Docker\Docker\Docker Desktop.exe"

if not exist "%LOG_DIR%" mkdir "%LOG_DIR%"

echo.
echo ============================================================
echo   LUZICITY - INICIALIZACAO COMPLETA
echo ============================================================
echo.

where php.exe >nul 2>&1
if errorlevel 1 (
    echo [ERRO] PHP nao foi encontrado no PATH.
    goto :failure
)

where npm.cmd >nul 2>&1
if errorlevel 1 (
    echo [ERRO] Node.js/NPM nao foi encontrado no PATH.
    goto :failure
)

where docker.exe >nul 2>&1
if errorlevel 1 (
    echo [ERRO] Docker CLI nao foi encontrado no PATH.
    goto :failure
)

if not exist "%PROJECT_ROOT%vendor\autoload.php" (
    echo [ERRO] Dependencias PHP ausentes. Execute: composer install
    goto :failure
)

if not exist "%PROJECT_ROOT%node_modules" (
    echo [INFO] Instalando dependencias Node.js...
    pushd "%PROJECT_ROOT%"
    call npm.cmd install
    if errorlevel 1 (
        popd
        echo [ERRO] Falha ao instalar dependencias Node.js.
        goto :failure
    )
    popd
)

echo [1/3] Verificando Docker Desktop e AzuraCast...
docker info >nul 2>&1
if errorlevel 1 (
    if not exist "%DOCKER_DESKTOP%" (
        echo [ERRO] Docker Desktop nao foi encontrado.
        goto :failure
    )

    echo [INFO] Iniciando Docker Desktop...
    start "" /min "%DOCKER_DESKTOP%"

    set "DOCKER_READY=0"
    for /L %%G in (1,1,24) do (
        if "!DOCKER_READY!"=="0" (
            timeout /t 5 /nobreak >nul
            docker info >nul 2>&1
            if not errorlevel 1 set "DOCKER_READY=1"
        )
    )

    if "!DOCKER_READY!"=="0" (
        echo [ERRO] Docker Desktop nao ficou pronto em 120 segundos.
        goto :failure
    )
)

call :port_listening 8080
if "!PORT_OPEN!"=="1" (
    echo [OK] AzuraCast ja esta ativo em http://127.0.0.1:8080
) else (
    if not exist "%AZURACAST_SCRIPT%" (
        echo [ERRO] Script do AzuraCast nao encontrado:
        echo        %AZURACAST_SCRIPT%
        goto :failure
    )

    echo [INFO] Iniciando containers do AzuraCast...
    powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%AZURACAST_SCRIPT%"
    if errorlevel 1 (
        echo [ERRO] Falha ao iniciar o AzuraCast.
        goto :failure
    )

    call :wait_port 8080 60
    if "!PORT_OPEN!"=="0" (
        echo [ERRO] AzuraCast nao respondeu na porta 8080.
        goto :failure
    )
    echo [OK] AzuraCast iniciado.
)

echo.
echo [2/3] Verificando Vite...
call :vite_healthy
if "!PORT_OPEN!"=="1" (
    echo [OK] Vite ja esta ativo na porta 5173.
    > "%PROJECT_ROOT%public\hot" echo http://127.0.0.1:5173
) else (
    call :port_listening 5173
    if "!PORT_OPEN!"=="1" (
        echo [INFO] Encontrada uma instancia Vite travada. Encerrando somente a porta 5173...
        for /f "tokens=5" %%P in ('netstat.exe -ano -p tcp ^| findstr /R /C:"127.0.0.1:5173 .*LISTENING"') do taskkill /PID %%P /T /F >nul 2>&1
        timeout /t 2 /nobreak >nul
    )

    echo [INFO] Iniciando Vite em uma janela separada...
    start "LuziCity - Vite" /min cmd.exe /c "cd /d ""%PROJECT_ROOT%"" && npm.cmd run dev -- --host 127.0.0.1 --port 5173 >> ""%LOG_DIR%\vite.log"" 2>&1"

    call :wait_vite 30
    if "!PORT_OPEN!"=="0" (
        if exist "%PROJECT_ROOT%public\hot" del /q "%PROJECT_ROOT%public\hot"
        echo [ERRO] Vite nao respondeu. O arquivo public\hot foi removido para preservar o CSS compilado.
        goto :failure
    )
    echo [OK] Vite iniciado em http://127.0.0.1:5173
)

echo.
echo [3/3] Verificando LuziCity...
call :port_listening 9001
if "!PORT_OPEN!"=="1" (
    echo [OK] LuziCity ja esta ativo na porta 9001.
) else (
    echo [INFO] Limpando caches do Laravel...
    pushd "%PROJECT_ROOT%"
    php artisan optimize:clear
    if errorlevel 1 (
        popd
        echo [ERRO] Falha ao preparar o Laravel.
        goto :failure
    )
    popd

    echo [INFO] Iniciando Laravel em uma janela separada...
    start "LuziCity - Laravel 9001" /min cmd.exe /c "cd /d ""%PROJECT_ROOT%"" && php artisan serve --host=127.0.0.1 --port=9001 >> ""%LOG_DIR%\laravel-serve.log"" 2>&1"

    call :wait_port 9001 30
    if "!PORT_OPEN!"=="0" (
        echo [ERRO] Laravel nao respondeu na porta 9001.
        goto :failure
    )
    echo [OK] LuziCity iniciado.
)

echo.
echo ============================================================
echo   TODOS OS SERVICOS ESTAO ATIVOS
echo ============================================================
echo   Site:       http://127.0.0.1:9001
echo   Admin radio:http://127.0.0.1:9001/admin/radio
echo   Radio:      http://127.0.0.1:9001/radio
echo   AzuraCast:  http://127.0.0.1:8080
echo   Vite:       http://127.0.0.1:5173
echo ============================================================
echo.

if /I not "%~1"=="--no-open" start "" "http://127.0.0.1:9001"
echo Esta janela pode ser fechada. Os servicos continuarao ativos.
pause
exit /b 0

:port_listening
set "PORT_OPEN=0"
for /f "tokens=*" %%L in ('netstat.exe -ano -p tcp ^| findstr /R /C:":%~1 .*LISTENING"') do set "PORT_OPEN=1"
exit /b 0

:wait_port
set "PORT_OPEN=0"
set /a "WAIT_SECONDS=%~2"
for /L %%S in (1,1,!WAIT_SECONDS!) do (
    if "!PORT_OPEN!"=="0" (
        call :port_listening %~1
        if "!PORT_OPEN!"=="0" timeout /t 1 /nobreak >nul
    )
)
exit /b 0

:vite_healthy
set "PORT_OPEN=0"
curl.exe --silent --fail --max-time 3 "http://127.0.0.1:5173/@vite/client" >nul 2>&1
if not errorlevel 1 set "PORT_OPEN=1"
exit /b 0

:wait_vite
set "PORT_OPEN=0"
set /a "WAIT_SECONDS=%~1"
for /L %%S in (1,1,!WAIT_SECONDS!) do (
    if "!PORT_OPEN!"=="0" (
        call :vite_healthy
        if "!PORT_OPEN!"=="0" timeout /t 1 /nobreak >nul
    )
)
exit /b 0

:failure
echo.
echo A inicializacao foi interrompida. Corrija o erro acima e execute
echo novamente o arquivo INICIAR-LUZICITY-COMPLETO.bat.
echo.
pause
exit /b 1
