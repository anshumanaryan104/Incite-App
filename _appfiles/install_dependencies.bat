@echo off
echo Installing Laravel Dependencies...
cd /d "C:\Users\ANSHUMAN ARYAN\projects\news_app\_appfiles"

echo.
echo Step 1: Downloading Composer...
C:\xampp\php\php.exe -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
C:\xampp\php\php.exe composer-setup.php
C:\xampp\php\php.exe -r "unlink('composer-setup.php');"

echo.
echo Step 2: Installing Laravel dependencies...
C:\xampp\php\php.exe composer.phar install --no-scripts

echo.
echo Step 3: Running post-install scripts...
C:\xampp\php\php.exe composer.phar run-script post-install-cmd

echo.
echo Step 4: Generating application key...
C:\xampp\php\php.exe artisan key:generate

echo.
echo Step 5: Starting server...
C:\xampp\php\php.exe artisan serve

pause