@echo off
REM ================================================
REM Auto Git Push Script for frontoffice-6310
REM ================================================

cd /d "d:\2026\Google App Script\frontoffice\laravel\frontoffice-6310"

REM Get current date and time for commit message
for /f "tokens=1-4 delims=/ " %%a in ('date /t') do set DATE=%%a-%%b-%%c
for /f "tokens=1-2 delims=: " %%a in ('time /t') do set TIME=%%a:%%b

echo ================================================
echo   Auto Git Push - %DATE% %TIME%
echo ================================================
echo.

REM Check git status
echo [1/4] Checking git status...
git status --short
echo.

REM Add all changes
echo [2/4] Adding all changes...
git add .
echo.

REM Commit with auto-generated message or custom message
if "%1"=="" (
    set COMMIT_MSG=Auto update: %DATE% %TIME%
) else (
    set COMMIT_MSG=%*
)

echo [3/4] Committing: %COMMIT_MSG%
git commit -m "%COMMIT_MSG%"
echo.

REM Push to remote
echo [4/4] Pushing to remote...
git push origin main
echo.

echo ================================================
echo   Done! Repository updated successfully.
echo ================================================
pause
