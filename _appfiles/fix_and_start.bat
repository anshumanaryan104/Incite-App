@echo off
echo Fixing Laravel Setup...
cd /d "C:\Users\ANSHUMAN ARYAN\projects\news_app\_appfiles"

echo.
echo Step 1: Taking ownership of directories...
takeown /f bootstrap\cache /r /d y >nul 2>&1
takeown /f storage /r /d y >nul 2>&1

echo Step 2: Removing read-only attributes...
attrib -r bootstrap\cache\*.* /s >nul 2>&1
attrib -r storage\*.* /s >nul 2>&1

echo Step 3: Clearing any cache...
C:\xampp\php\php.exe artisan config:clear 2>nul
C:\xampp\php\php.exe artisan cache:clear 2>nul
C:\xampp\php\php.exe artisan view:clear 2>nul

echo Step 4: Creating required directories...
mkdir storage\framework\cache 2>nul
mkdir storage\framework\sessions 2>nul
mkdir storage\framework\views 2>nul
mkdir storage\logs 2>nul

echo.
echo Starting Laravel Server...
C:\xampp\php\php.exe artisan serve --host=127.0.0.1 --port=8000

pause