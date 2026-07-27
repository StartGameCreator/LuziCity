@echo off
setlocal
cd /d "%~dp0"
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\corrigir-testes-search-sitemap.ps1"
set "EXITCODE=%ERRORLEVEL%"
echo.
if not "%EXITCODE%"=="0" (
    echo A correcao terminou com erro. Consulte as mensagens acima.
) else (
    echo Correcao instalada e testes validados.
)
pause
exit /b %EXITCODE%
