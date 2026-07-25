@echo off
setlocal EnableExtensions
title LuziCity - Fase 13.1 Gestao de Anunciantes

set "PROJECT=D:\Skill\LuziCity"
set "PATCHDIR=%~dp0"

if exist "%PATCHDIR%artisan" set "PROJECT=%PATCHDIR:~0,-1%"

if not exist "%PROJECT%\artisan" (
    echo.
    echo [ERRO] O arquivo artisan nao foi encontrado em:
    echo %PROJECT%
    echo.
    echo Extraia o patch e execute este BAT novamente.
    pause
    exit /b 1
)

pushd "%PROJECT%"
if errorlevel 1 (
    echo [ERRO] Nao foi possivel acessar %PROJECT%
    pause
    exit /b 1
)

for /f "tokens=2 delims==" %%I in ('wmic os get localdatetime /value 2^>nul ^| find "="') do set "NOW=%%I"
if not defined NOW set "NOW=20260725-000000"
set "STAMP=%NOW:~0,8%-%NOW:~8,6%"
set "BACKUP=%PROJECT%\backups\antes-fase13-1-%STAMP%"

echo.
echo ==================================================
echo  LUZICITY - INSTALACAO FASE 13.1
echo ==================================================
echo Projeto: %PROJECT%
echo Patch:   %PATCHDIR%
echo Backup:  %BACKUP%
echo.

mkdir "%BACKUP%" 2>nul
if errorlevel 1 goto :FAIL

call :BACKUP_FILE "routes\web.php"
call :BACKUP_FILE "app\Http\Controllers\AdminDashboardController.php"
call :BACKUP_FILE "resources\views\layouts\app.blade.php"
call :BACKUP_FILE "app\Models\AdvertiserProfile.php"

if exist "database\database.sqlite" (
    copy /y "database\database.sqlite" "%BACKUP%\database.sqlite" >nul
    if errorlevel 1 goto :FAIL
)

call :COPY_DIR "app"
if errorlevel 1 goto :FAIL
call :COPY_DIR "database"
if errorlevel 1 goto :FAIL
call :COPY_DIR "resources"
if errorlevel 1 goto :FAIL
call :COPY_DIR "routes"
if errorlevel 1 goto :FAIL

if not exist "%PATCHDIR%scripts\aplicar-integracoes.php" goto :FAIL
php "%PATCHDIR%scripts\aplicar-integracoes.php"
if errorlevel 1 goto :FAIL

echo.
echo Validando arquivos PHP do patch...
for /r "%PATCHDIR%app" %%F in (*.php) do (
    php -l "%%F" >nul
    if errorlevel 1 goto :FAIL
)
for /r "%PATCHDIR%routes" %%F in (*.php) do (
    php -l "%%F" >nul
    if errorlevel 1 goto :FAIL
)
for /r "%PATCHDIR%database\migrations" %%F in (*.php) do (
    php -l "%%F" >nul
    if errorlevel 1 goto :FAIL
)

php artisan optimize:clear
if errorlevel 1 goto :FAIL
php artisan migrate --force
if errorlevel 1 goto :FAIL
php artisan route:list --name=admin.advertisers
if errorlevel 1 goto :FAIL
php artisan view:clear
if errorlevel 1 goto :FAIL
php artisan view:cache
if errorlevel 1 goto :FAIL
php artisan optimize:clear
if errorlevel 1 goto :FAIL

echo.
echo ==================================================
echo  FASE 13.1 INSTALADA COM SUCESSO
echo ==================================================
echo Backup: %BACKUP%
echo Acesso: http://127.0.0.1:9001/admin/comercial/anunciantes
echo.
popd
pause
exit /b 0

:BACKUP_FILE
set "REL=%~1"
if exist "%PROJECT%\%REL%" (
    for %%D in ("%BACKUP%\%REL%") do if not exist "%%~dpD" mkdir "%%~dpD" >nul 2>&1
    copy /y "%PROJECT%\%REL%" "%BACKUP%\%REL%" >nul
    if errorlevel 1 exit /b 1
)
exit /b 0

:COPY_DIR
set "DIRNAME=%~1"
if exist "%PATCHDIR%%DIRNAME%" (
    xcopy "%PATCHDIR%%DIRNAME%\*" "%PROJECT%\%DIRNAME%\" /E /I /Y /Q >nul
    if errorlevel 1 exit /b 1
)
exit /b 0

:FAIL
echo.
echo ==================================================
echo  ERRO: INSTALACAO INTERROMPIDA
echo ==================================================
echo Consulte o backup em:
echo %BACKUP%
echo.
popd
pause
exit /b 1
