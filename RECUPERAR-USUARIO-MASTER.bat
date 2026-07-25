@echo off
setlocal EnableExtensions
title LuziCity - Recuperar Usuario Master

cd /d "%~dp0"

echo.
echo ==============================================
echo  LUZICITY - RECUPERAR USUARIO MASTER
echo ==============================================
echo.

if not exist "artisan" (
    echo [ERRO] Este arquivo deve estar na raiz do projeto LuziCity.
    echo        Nao foi encontrado o arquivo artisan.
    echo.
    pause
    exit /b 1
)

if not exist "vendor\autoload.php" (
    echo [ERRO] Dependencias do Composer nao encontradas.
    echo        Execute: composer install
    echo.
    pause
    exit /b 1
)

if not exist "database\database.sqlite" (
    echo [AVISO] database\database.sqlite nao foi encontrado.
    echo         O projeto pode estar usando outro banco configurado no .env.
    echo.
)

set "BACKUP_DIR=database\backups"
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

for /f "tokens=1-4 delims=/ " %%a in ("%date%") do (
    set "DATESTAMP=%%d%%c%%b"
)
for /f "tokens=1-3 delims=:,." %%a in ("%time%") do (
    set "TIMESTAMP=%%a%%b%%c"
)
set "TIMESTAMP=%TIMESTAMP: =0%"

if exist "database\database.sqlite" (
    copy /y "database\database.sqlite" "%BACKUP_DIR%\database_antes_recuperar_master_%DATESTAMP%_%TIMESTAMP%.sqlite" >nul
    if errorlevel 1 (
        echo [ERRO] Nao foi possivel criar o backup do banco.
        pause
        exit /b 1
    )
    echo [OK] Backup criado em %BACKUP_DIR%.
)

echo.
echo Limpando caches do Laravel...
php artisan optimize:clear
if errorlevel 1 (
    echo [ERRO] Falha ao limpar os caches.
    pause
    exit /b 1
)

echo.
echo Recriando o usuario Elvis e atribuindo o papel Master...
php RECUPERAR-USUARIO-MASTER.php
if errorlevel 1 (
    echo.
    echo [ERRO] A recuperacao nao foi concluida.
    echo O banco original permanece no backup criado.
    pause
    exit /b 1
)

echo.
echo Limpando novamente os caches...
php artisan optimize:clear

echo.
echo ==============================================
echo  PROCESSO CONCLUIDO
echo ==============================================
echo.
echo E-mail: elvis@luzicity.com.br
echo Senha : Start@Game357
echo Papel : Master
echo.
echo Agora inicie o servidor e tente entrar novamente.
echo.
pause
exit /b 0
