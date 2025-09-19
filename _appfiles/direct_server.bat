@echo off
echo Starting PHP Development Server directly (bypassing artisan)...
cd /d "C:\Users\ANSHUMAN ARYAN\projects\news_app\_appfiles"
echo.
echo Server starting at http://localhost:8000
echo Press Ctrl+C to stop
echo.
C:\xampp\php\php.exe -S localhost:8000 -t public
pause