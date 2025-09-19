@echo off
echo Starting Laravel Server...
cd /d "C:\Users\ANSHUMAN ARYAN\projects\news_app\_appfiles"
echo Current directory: %cd%
echo.
echo Checking for artisan file...
if exist artisan (
    echo Artisan found! Starting server...
    C:\xampp\php\php.exe artisan serve --host=127.0.0.1 --port=8000
) else (
    echo ERROR: artisan file not found in current directory!
    echo Please make sure you are in the Laravel project root directory.
    dir
)
pause