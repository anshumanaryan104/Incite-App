@echo off
echo Fixing Laravel permissions...

echo Creating cache directory if not exists...
if not exist "bootstrap\cache" mkdir "bootstrap\cache"

echo Clearing cache files...
del /q bootstrap\cache\* 2>nul

echo Setting permissions...
icacls bootstrap\cache /grant Everyone:F /T
icacls storage /grant Everyone:F /T

echo Creating storage directories...
if not exist "storage\framework\cache" mkdir "storage\framework\cache"
if not exist "storage\framework\sessions" mkdir "storage\framework\sessions"
if not exist "storage\framework\views" mkdir "storage\framework\views"

echo Permissions fixed!
echo.
echo Now starting server...
C:\xampp\php\php.exe artisan serve
pause