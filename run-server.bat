@echo off
chcp 65001 >nul
set PHP_BIN=D:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe
if not exist "%PHP_BIN%" set PHP_BIN=php

echo ========================================================
echo   KHOI TAO DATABASE VA CHAY SERVER CINEMA COMPLETE
echo ========================================================
"%PHP_BIN%" setup.php
echo.
echo Dang khoi dong PHP Server tai: http://localhost:8000
echo Nhan Ctrl+C de dung server.
echo.
"%PHP_BIN%" -S localhost:8000

