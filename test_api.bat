@echo off
echo Testing API endpoints...
echo.
curl http://localhost:8000/api/blog-list
echo.
echo.
curl http://10.0.2.2:8000/api/blog-list
pause