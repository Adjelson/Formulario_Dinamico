@echo off
setlocal enabledelayedexpansion
set ROOT=%~dp0..
for /R "%ROOT%" %%F in (*.php) do (
  php -l "%%F" >nul
  if errorlevel 1 exit /b 1
)
php "%ROOT%\tests\run.php"
if errorlevel 1 exit /b 1
echo Validacao concluida.
