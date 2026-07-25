@echo off
setlocal EnableExtensions
title LuziCity - Master para Super Admin

set "PROJECT=D:\Skill\LuziCity"

if exist "%~dp0artisan" (
    set "PROJECT=%~dp0"
)

cd /d "%PROJECT%"

echo.
echo ==================================================
echo  LUZICITY - ALTERAR MASTER PARA SUPER ADMIN
echo ==================================================
echo.

if not exist "artisan" (
    echo [ERRO] O projeto LuziCity nao foi encontrado.
    echo Caminho procurado: %PROJECT%
    echo.
    echo Coloque este BAT na raiz do projeto ou confirme:
    echo D:\Skill\LuziCity
    echo.
    pause
    exit /b 1
)

if not exist "vendor\autoload.php" (
    echo [ERRO] Dependencias do Composer nao encontradas.
    echo Execute "composer install" antes de continuar.
    echo.
    pause
    exit /b 1
)

if not exist "ALTERAR-MASTER-PARA-SUPER-ADMIN.php" (
    echo [ERRO] O arquivo ALTERAR-MASTER-PARA-SUPER-ADMIN.php
    echo nao foi encontrado ao lado deste BAT.
    echo.
    pause
    exit /b 1
)

set "BACKUP_DIR=database\backups"
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

for /f "tokens=1-4 delims=/ " %%a in ("%date%") do set "DATESTAMP=%%d%%c%%b"
for /f "tokens=1-3 delims=:,." %%a in ("%time%") do set "TIMESTAMP=%%a%%b%%c"
set "TIMESTAMP=%TIMESTAMP: =0%"

if exist "database\database.sqlite" (
    copy /y "database\database.sqlite" "%BACKUP_DIR%\database_antes_super_admin_%DATESTAMP%_%TIMESTAMP%.sqlite" >nul
    if errorlevel 1 (
        echo [ERRO] Nao foi possivel criar o backup do banco.
        pause
        exit /b 1
    )
    echo [OK] Backup do banco criado.
)

echo.
echo Limpando caches...
php artisan optimize:clear
if errorlevel 1 (
    echo [ERRO] Falha ao limpar os caches do Laravel.
    pause
    exit /b 1
)

echo.
echo Alterando Master para Super Admin...
php "%~dp0ALTERAR-MASTER-PARA-SUPER-ADMIN.php"
if errorlevel 1 (
    echo.
    echo [ERRO] A alteracao nao foi concluida.
    echo O backup do banco foi preservado em %BACKUP_DIR%.
    pause
    exit /b 1
)

echo.
echo Limpando cache de permissoes...
php artisan permission:cache-reset
php artisan optimize:clear

echo.
echo Conferindo papeis e permissoes...
php artisan permission:show

echo.
echo ==================================================
echo  PROCESSO CONCLUIDO
echo ==================================================
echo.
echo Usuario: elvis@luzicity.com.br
echo Papel  : Super Admin
echo.
echo Acesse:
echo http://127.0.0.1:9001/admin
echo.
pause
exit /b 0
