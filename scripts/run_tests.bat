@echo off
REM Run PHPUnit Tests
REM Usage: run_tests.bat

echo ================================
echo Running PHPUnit Tests...
echo ================================

C:\xampp\php\php.exe C:\xampp\htdocs\app\vendor\bin\phpunit --configuration C:\xampp\htdocs\app\phpunit.xml --colors=always

echo.
echo ================================
echo Test run complete!
echo ================================
