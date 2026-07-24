@echo off
cd /d "%~dp0"
set "PHPRC=%~dp0php-luzicity.ini"
set "LOG=%~dp0instalacao_laravel13.log"
echo ==========================================
echo  LuziCityLaravel13 - instalar dependencias
echo ==========================================
echo.
echo Esta etapa instala o Laravel e as dependencias dentro desta copia.
echo O projeto original luzicity nao sera alterado.
echo.
echo Log desta instalacao: %LOG%
echo.
echo ===== LuziCityLaravel13 instalacao ===== > "%LOG%"
echo Pasta: %CD% >> "%LOG%"
echo Data: %DATE% %TIME% >> "%LOG%"
echo. >> "%LOG%"
if not exist database\database.sqlite (
    type nul > database\database.sqlite
)
echo Conferindo PHP desta copia...
php --ini >> "%LOG%" 2>&1
php -m >> "%LOG%" 2>&1
php -m | findstr /I curl >nul
if errorlevel 1 (
    echo.
    echo ERRO: a extensao curl ainda nao foi carregada.
    echo Abra o arquivo instalacao_laravel13.log para ver os detalhes.
    pause
    exit /b 1
)
echo Instalando dependencias com Composer...
call composer install >> "%LOG%" 2>&1
if errorlevel 1 (
    echo.
    echo ERRO: o Composer nao conseguiu instalar as dependencias.
    echo Abra este arquivo para ver o motivo:
    echo %LOG%
    pause
    exit /b 1
)
echo Preparando Laravel...
php -c "%~dp0php-luzicity.ini" artisan key:generate --force >> "%LOG%" 2>&1
if errorlevel 1 goto artisan_error
php -c "%~dp0php-luzicity.ini" artisan migrate --force >> "%LOG%" 2>&1
if errorlevel 1 goto artisan_error
php -c "%~dp0php-luzicity.ini" artisan optimize:clear >> "%LOG%" 2>&1
if errorlevel 1 goto artisan_error
echo.
echo Dependencias preparadas. Agora use 2_iniciar_luzicity_laravel13_9001.bat.
pause
exit /b 0

:artisan_error
echo.
echo ERRO: o Laravel encontrou um problema durante a preparacao.
echo Abra este arquivo para ver o motivo:
echo %LOG%
pause
exit /b 1
