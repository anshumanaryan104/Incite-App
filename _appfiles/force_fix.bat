@echo off
echo Force fixing cache issue...
cd /d "C:\Users\ANSHUMAN ARYAN\projects\news_app\_appfiles"

echo.
echo Removing old cache folder...
rmdir /s /q bootstrap\cache 2>nul

echo Creating new cache folder...
mkdir bootstrap\cache

echo Creating .gitignore in cache...
echo * > bootstrap\cache\.gitignore
echo !.gitignore >> bootstrap\cache\.gitignore

echo.
echo Testing write permission...
echo test > bootstrap\cache\test.txt
if exist bootstrap\cache\test.txt (
    echo SUCCESS: Cache folder is writable!
    del bootstrap\cache\test.txt
) else (
    echo ERROR: Cannot write to cache folder
)

echo.
echo Dumping autoload...
C:\xampp\php\php.exe composer.phar dump-autoload

echo.
echo Starting Laravel server on port 8000...
C:\xampp\php\php.exe artisan serve --host=127.0.0.1 --port=8000

pause