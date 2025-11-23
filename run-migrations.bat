@echo off
echo Running Laravel Migrations...
echo.

REM Try to find PHP in common locations
set PHP_PATH=

REM Check if PHP is in PATH
where php >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    set PHP_PATH=php
    goto :run_migrations
)

REM Check common PHP installation paths
if exist "C:\php\php.exe" set PHP_PATH=C:\php\php.exe
if exist "C:\xampp\php\php.exe" set PHP_PATH=C:\xampp\php\php.exe
if exist "C:\wamp\bin\php\php8.1\php.exe" set PHP_PATH=C:\wamp\bin\php\php8.1\php.exe
if exist "C:\laragon\bin\php\php-8.1\php.exe" set PHP_PATH=C:\laragon\bin\php\php-8.1\php.exe
if exist "C:\Program Files\PHP\php.exe" set PHP_PATH=C:\Program Files\PHP\php.exe

if "%PHP_PATH%"=="" (
    echo ERROR: PHP not found!
    echo.
    echo Please either:
    echo 1. Add PHP to your system PATH, OR
    echo 2. Edit this file and set PHP_PATH to your PHP executable location
    echo.
    echo Common locations:
    echo   - C:\php\php.exe
    echo   - C:\xampp\php\php.exe
    echo   - C:\wamp\bin\php\php8.1\php.exe
    echo   - C:\laragon\bin\php\php-8.1\php.exe
    echo.
    pause
    exit /b 1
)

:run_migrations
echo Using PHP: %PHP_PATH%
echo.
%PHP_PATH% artisan migrate
echo.
echo Migrations completed!
pause

