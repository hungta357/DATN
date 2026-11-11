@echo off
chcp 65001 >nul
cd /d "%~dp0"

set PHP_BIN=D:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe
if not exist "%PHP_BIN%" set PHP_BIN=php

echo ========================================================
echo   KHOI TAO DATABASE VA CHAY SERVER BETA CINEMA
echo ========================================================
"%PHP_BIN%" setup.php
echo.
echo Dang mo trinh duyet tai: http://localhost:8000
start http://localhost:8000
echo Dang khoi dong PHP Server tai: http://localhost:8000
echo (Giu cua so nay mo khi su dung web. Nhan Ctrl+C de dung server)
echo.
"%PHP_BIN%" -S localhost:8000 -t "%~dp0"
pause
