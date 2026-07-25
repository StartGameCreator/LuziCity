@echo off
setlocal EnableExtensions EnableDelayedExpansion
chcp 65001 >nul
set "PROJECT=D:\Skill\LuziCity"
set "PATCH=%~dp0"
set "STAMP=%DATE:~-4%%DATE:~3,2%%DATE:~0,2%-%TIME:~0,2%%TIME:~3,2%%TIME:~6,2%"
set "STAMP=%STAMP: =0%"
set "BACKUP=%PROJECT%\backups\antes-auditoria-banco-%STAMP%"

if not exist "%PROJECT%\artisan" (
  echo ERRO: Projeto nao encontrado em %PROJECT%
  pause
  exit /b 1
)

php "%PATCH%scripts\verify_patch.php" "%PROJECT%" || goto :erro

mkdir "%BACKUP%\app\Console\Commands" 2>nul
mkdir "%BACKUP%\app\Services\Database" 2>nul
mkdir "%BACKUP%\database\migrations" 2>nul

for %%F in (
  "app\Console\Commands\AuditDatabaseCommand.php"
  "app\Services\Database\DatabaseHealthService.php"
  "database\migrations\2026_07_25_280000_consolidate_database_growth.php"
) do (
  if exist "%PROJECT%\%%~F" copy /Y "%PROJECT%\%%~F" "%BACKUP%\%%~F" >nul
)

xcopy /E /I /Y "%PATCH%app" "%PROJECT%\app" >nul || goto :erro
xcopy /E /I /Y "%PATCH%database" "%PROJECT%\database" >nul || goto :erro

cd /d "%PROJECT%"
php artisan optimize:clear || goto :erro
php artisan migrate --force || goto :erro
php artisan luzicity:database-audit
set "AUDIT_EXIT=%ERRORLEVEL%"
php artisan route:list --no-ansi > "%BACKUP%\rotas-apos-patch.txt"
php artisan view:clear || goto :erro
php artisan view:cache || goto :erro
php artisan optimize:clear || goto :erro

echo.
echo Patch instalado. Backup: %BACKUP%
if not "%AUDIT_EXIT%"=="0" echo ATENCAO: a auditoria encontrou itens para revisao. Consulte a saida acima.
pause
exit /b 0

:erro
echo.
echo ERRO durante a instalacao. Nenhum banco foi apagado.
echo Backup disponivel em: %BACKUP%
pause
exit /b 1
