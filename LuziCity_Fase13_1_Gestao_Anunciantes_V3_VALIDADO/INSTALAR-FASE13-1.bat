@echo off
setlocal EnableExtensions
chcp 65001 >nul
set "SCRIPT=%~dp0INSTALAR-FASE13-1.ps1"
if not exist "%SCRIPT%" (
  echo [ERRO] INSTALAR-FASE13-1.ps1 nao foi encontrado.
  pause
  exit /b 1
)
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%SCRIPT%"
set "RC=%ERRORLEVEL%"
if not "%RC%"=="0" (
  echo.
  echo [ERRO] A instalacao terminou com codigo %RC%.
  pause
  exit /b %RC%
)
pause
exit /b 0
