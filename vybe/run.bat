@echo off
echo Starting Vybe Social App (PHP Built-in Server + SQLite Fallback if needed)
echo 1. Open http://localhost:8000/public/
echo 2. Login johnp or register new
echo 3. Test posts/likes etc.

php -S localhost:8000 -t public/
pause
