@echo off
chcp 65001 >nul
cd /d "%~dp0"

echo ==========================================
echo  Luzicity - Teste de RSS e Internet
echo ==========================================
echo.
echo Este teste confere se o Windows/PHP consegue acessar sites HTTPS
echo e depois tenta importar as noticias RSS para o banco.
echo.

echo [1/3] Testando porta 443 para o Google...
powershell -NoProfile -ExecutionPolicy Bypass -Command "Test-NetConnection www.google.com -Port 443 | Select-Object ComputerName,RemoteAddress,TcpTestSucceeded"
echo.

echo [2/3] Testando porta 443 para o G1...
powershell -NoProfile -ExecutionPolicy Bypass -Command "Test-NetConnection g1.globo.com -Port 443 | Select-Object ComputerName,RemoteAddress,TcpTestSucceeded"
echo.

echo [3/3] Tentando importar RSS...
php -c "%~dp0php-luzicity.ini" artisan luzicity:import-rss --limit=12
echo.

echo Se aparecer TcpTestSucceeded = False, a internet do navegador pode estar ok,
echo mas o PHP/Windows esta bloqueado para buscar RSS por HTTPS.
echo.
pause
