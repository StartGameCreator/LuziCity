@echo off
setlocal EnableExtensions
chcp 65001 >nul
set "PROJECT=D:\Skill\LuziCity"
set "PATCH=%~dp0"
cd /d "%PROJECT%"
if not exist artisan (echo [ERRO] artisan não encontrado em %PROJECT% & pause & exit /b 1)
for /f "tokens=1-3 delims=/ " %%a in ("%date%") do set "D=%%c%%b%%a"
for /f "tokens=1-3 delims=:,. " %%a in ("%time%") do set "T=%%a%%b%%c"
set "T=%T: =0%"
set "BACKUP=%PROJECT%\backups\antes-fase13-1-%D%-%T%"
mkdir "%BACKUP%" || exit /b 1
for %%F in (routes\web.php app\Http\Controllers\AdminDashboardController.php resources\views\layouts\app.blade.php app\Models\AdvertiserProfile.php) do (if exist "%%F" (mkdir "%BACKUP%\%%~dpF" 2>nul & copy /y "%%F" "%BACKUP%\%%F" >nul))
if exist database\database.sqlite copy /y database\database.sqlite "%BACKUP%\database.sqlite" >nul
xcopy "%PATCH%app" "%PROJECT%\app" /E /I /Y >nul || goto :fail
xcopy "%PATCH%database" "%PROJECT%\database" /E /I /Y >nul || goto :fail
xcopy "%PATCH%resources" "%PROJECT%\resources" /E /I /Y >nul || goto :fail
xcopy "%PATCH%routes" "%PROJECT%\routes" /E /I /Y >nul || goto :fail
php "%PATCH%scripts\aplicar-integracoes.php" || goto :fail
for /r app %%F in (*.php) do php -l "%%F" >nul || goto :fail
for /r routes %%F in (*.php) do php -l "%%F" >nul || goto :fail
for /r database\migrations %%F in (*.php) do php -l "%%F" >nul || goto :fail
php artisan optimize:clear || goto :fail
php artisan migrate --force || goto :fail
php artisan route:list --name=admin.advertisers || goto :fail
php artisan view:clear || goto :fail
php artisan view:cache || goto :fail
php artisan optimize:clear || goto :fail
echo.
echo [OK] Fase 13.1 instalada. Backup: %BACKUP%
echo Acesse http://127.0.0.1:9001/admin/comercial/anunciantes
pause
exit /b 0
:fail
echo.
echo [ERRO] Instalação interrompida. Use ABRIR-BACKUP-PARA-ROLLBACK.bat.
pause
exit /b 1
