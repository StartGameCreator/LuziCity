@echo off
cd /d "%~dp0"
set "PHPRC=%~dp0php-luzicity.ini"
echo ==========================================
echo  Iniciando LuziCityLaravel13 na porta 9001
echo ==========================================
echo.
echo Endereco do teste: http://127.0.0.1:9001
echo Para parar o servidor, pressione CTRL+C neste terminal.
echo.
if not exist vendor\autoload.php (
    echo As dependencias ainda nao foram instaladas nesta copia.
    echo Execute primeiro: 1_instalar_dependencias_laravel13.bat
    pause
    exit /b 1
)
php -c "%~dp0php-luzicity.ini" -S 127.0.0.1:9001 luzicity-server.php
pause
