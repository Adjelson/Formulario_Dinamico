@echo off
setlocal
cd /d "%~dp0"
where docker >nul 2>nul || (
  echo ERRO: Docker Desktop nao foi encontrado.
  echo Instale e abra o Docker Desktop antes de executar este ficheiro.
  pause
  exit /b 1
)
if not exist .env copy /Y .env.example .env >nul
docker compose up --build -d
if errorlevel 1 (
  echo ERRO: nao foi possivel iniciar os contentores.
  docker compose logs --tail=80
  pause
  exit /b 1
)
powershell -NoProfile -ExecutionPolicy Bypass -Command "$url='http://localhost:8080/health'; for($i=0;$i -lt 60;$i++){try{$r=Invoke-WebRequest -UseBasicParsing -Uri $url -TimeoutSec 2;if($r.StatusCode -eq 200){exit 0}}catch{};Start-Sleep -Seconds 1};exit 1"
if errorlevel 1 (
  echo A aplicacao foi iniciada, mas a verificacao de saude ainda nao respondeu.
  docker compose ps
  pause
  exit /b 1
)
start "" "http://localhost:8080"
echo Dynamic Forms iniciado em http://localhost:8080
exit /b 0
