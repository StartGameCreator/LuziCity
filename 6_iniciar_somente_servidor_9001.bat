@echo off
cd /d "%~dp0"
set "PHPRC=%~dp0php-luzicity.ini"
set "PHP_EXE=C:\tools\php85\php.exe"
set "PORT=9001"

echo ==========================================
echo  Iniciando LuziCityLaravel13 na porta 9001
echo ==========================================
echo.
echo Endereco: http://127.0.0.1:%PORT%
echo Para parar, pressione CTRL+C nesta janela.
echo.

if not exist "%PHP_EXE%" (
    echo ERRO: PHP nao encontrado em:
    echo %PHP_EXE%
    pause
    exit /b 1
)

"%PHP_EXE%" -c "%~dp0php-luzicity.ini" -S 127.0.0.1:%PORT% luzicity-server.php
pause
