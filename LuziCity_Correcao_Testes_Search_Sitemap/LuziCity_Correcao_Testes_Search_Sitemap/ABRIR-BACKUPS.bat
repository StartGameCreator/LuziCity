@echo off
setlocal
set "ROOT=D:\Skill\LuziCity"
if not exist "%ROOT%\backups" (
    echo Pasta de backups nao encontrada.
    pause
    exit /b 1
)
explorer "%ROOT%\backups"
