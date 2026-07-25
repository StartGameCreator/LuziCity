@echo off
setlocal EnableExtensions
title LuziCity - Abrir Backup Fase 13.1
set "BACKUPS=D:\Skill\LuziCity\backups"
if not exist "%BACKUPS%" (
    echo [ERRO] Pasta de backups nao encontrada: %BACKUPS%
    pause
    exit /b 1
)
start "" explorer "%BACKUPS%"
exit /b 0
