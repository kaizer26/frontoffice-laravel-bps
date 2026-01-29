@echo off
title Laravel Front Office Server
echo ---------------------------------------------------------
echo MENCARI ALAMAT IP KOMPUTER ANDA...
echo ---------------------------------------------------------
for /f "tokens=14" %%a in ('ipconfig ^| findstr IPv4') do set _IPADDR=%%a
echo IP Lokal Anda: %_IPADDR%
echo.
echo Untuk akses dari komputer lain, gunakan alamat:
echo http://%_IPADDR%:8001
echo ---------------------------------------------------------
echo MENJALANKAN SERVER...
echo (Tekan Ctrl+C untuk mematikan server)
echo.
php artisan serve --host=0.0.0.0 --port=8001
pause
