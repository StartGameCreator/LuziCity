@echo off
setlocal

net session >nul 2>&1
if %errorlevel% neq 0 (
    echo Solicitando permissao de administrador...
    powershell -NoProfile -ExecutionPolicy Bypass -Command "Start-Process -FilePath '%~f0' -Verb RunAs"
    exit /b
)

echo ==========================================
echo  Luzicity - liberar OpenAI no Firewall
echo ==========================================
echo.

set "PHP_EXE=C:\tools\php85\php.exe"
set "OPENAI_HOST=api.openai.com"

if not exist "%PHP_EXE%" (
    echo ERRO: PHP nao encontrado em:
    echo %PHP_EXE%
    echo.
    pause
    exit /b 1
)

echo Removendo regras antigas, se existirem...
netsh advfirewall firewall delete rule name="Luzicity PHP OpenAI 443" >nul 2>&1
netsh advfirewall firewall delete rule name="Luzicity HTTPS OpenAI 443" >nul 2>&1

echo Criando regra para permitir o PHP...
netsh advfirewall firewall add rule name="Luzicity PHP OpenAI 443" dir=out action=allow program="%PHP_EXE%" enable=yes profile=any
if %errorlevel% neq 0 goto firewall_error

echo Criando regra para permitir HTTPS porta 443...
netsh advfirewall firewall add rule name="Luzicity HTTPS OpenAI 443" dir=out action=allow protocol=TCP remoteport=443 enable=yes profile=any
if %errorlevel% neq 0 goto firewall_error

echo.
echo Regras criadas com sucesso.
echo.
echo Testando conexao com %OPENAI_HOST% na porta 443...
powershell -NoProfile -ExecutionPolicy Bypass -Command "Test-NetConnection %OPENAI_HOST% -Port 443 | Select-Object ComputerName,RemoteAddress,RemotePort,TcpTestSucceeded | Format-List"

echo.
echo Se TcpTestSucceeded aparecer como True, a porta foi liberada neste computador.
echo Se continuar False, o bloqueio pode estar no antivirus, proxy, roteador ou operadora.
echo.
pause
exit /b 0

:firewall_error
echo.
echo ERRO: nao foi possivel criar a regra no Firewall.
echo Confirme a permissao de administrador e tente novamente.
echo.
pause
exit /b 1
