@echo off
setlocal

net session >nul 2>&1
if %errorlevel% neq 0 (
    echo Solicitando permissao de administrador...
    powershell -NoProfile -ExecutionPolicy Bypass -Command "Start-Process -FilePath '%~f0' -Verb RunAs"
    exit /b
)

cd /d "%~dp0"
set "PHPRC=%~dp0php-luzicity.ini"
set "PHP_EXE=C:\tools\php85\php.exe"
set "OPENAI_HOST=api.openai.com"
set "CERT_DIR=%~dp0storage\certs"
set "CERT_FILE=%CERT_DIR%\windows-ca-bundle.pem"
set "PORT=9001"

echo ==========================================
echo  Luzicity - corrigir OpenAI e reiniciar
echo ==========================================
echo.

if not exist "%PHP_EXE%" (
    echo ERRO: PHP nao encontrado em:
    echo %PHP_EXE%
    pause
    exit /b 1
)

echo 1/5 - Atualizando certificados do PHP...
if not exist "%CERT_DIR%" mkdir "%CERT_DIR%"
powershell -NoProfile -ExecutionPolicy Bypass -Command "$bundle = '%CERT_FILE%'; '' | Set-Content -LiteralPath $bundle -Encoding ascii; $stores = @('Cert:\LocalMachine\Root','Cert:\CurrentUser\Root','Cert:\LocalMachine\CA','Cert:\CurrentUser\CA'); foreach ($store in $stores) { Get-ChildItem $store -ErrorAction SilentlyContinue | Where-Object { $_.NotAfter -gt (Get-Date) } | ForEach-Object { $base64 = [Convert]::ToBase64String($_.RawData, [Base64FormattingOptions]::InsertLineBreaks); Add-Content -LiteralPath $bundle -Value '-----BEGIN CERTIFICATE-----' -Encoding ascii; Add-Content -LiteralPath $bundle -Value $base64 -Encoding ascii; Add-Content -LiteralPath $bundle -Value '-----END CERTIFICATE-----' -Encoding ascii; Add-Content -LiteralPath $bundle -Value '' -Encoding ascii } }"

echo 2/5 - Liberando PHP e HTTPS 443 no Firewall...
netsh advfirewall firewall delete rule name="Luzicity PHP OpenAI 443" >nul 2>&1
netsh advfirewall firewall delete rule name="Luzicity HTTPS OpenAI 443" >nul 2>&1
netsh advfirewall firewall add rule name="Luzicity PHP OpenAI 443" dir=out action=allow program="%PHP_EXE%" enable=yes profile=any
netsh advfirewall firewall add rule name="Luzicity HTTPS OpenAI 443" dir=out action=allow protocol=TCP remoteport=443 enable=yes profile=any

echo 3/5 - Parando servidor antigo da porta %PORT%, se existir...
powershell -NoProfile -ExecutionPolicy Bypass -Command "$listeners = Get-NetTCPConnection -LocalPort %PORT% -State Listen -ErrorAction SilentlyContinue; foreach ($item in $listeners) { Stop-Process -Id $item.OwningProcess -Force -ErrorAction SilentlyContinue }"

echo 4/5 - Limpando cache do Laravel...
"%PHP_EXE%" -c "%~dp0php-luzicity.ini" artisan optimize:clear

echo 5/5 - Testando OpenAI e abrindo servidor na porta %PORT%...
powershell -NoProfile -ExecutionPolicy Bypass -Command "Test-NetConnection %OPENAI_HOST% -Port 443 | Select-Object ComputerName,RemoteAddress,RemotePort,TcpTestSucceeded | Format-List"
echo.
echo Abrindo servidor nesta janela:
echo http://127.0.0.1:%PORT%
echo Para manter o site no ar, deixe esta janela aberta.
echo Para parar, pressione CTRL+C.
echo.
"%PHP_EXE%" -c "%~dp0php-luzicity.ini" -S 127.0.0.1:%PORT% luzicity-server.php
pause
