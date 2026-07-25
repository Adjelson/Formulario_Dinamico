#!/usr/bin/env sh
set -eu
cd "$(dirname "$0")"
command -v docker >/dev/null 2>&1 || { echo "Docker não encontrado." >&2; exit 1; }
[ -f .env ] || cp .env.example .env
docker compose up --build -d
i=0
until curl -fsS http://localhost:8080/health >/dev/null 2>&1; do
  i=$((i + 1))
  [ "$i" -ge 60 ] && { docker compose ps; exit 1; }
  sleep 1
done
printf '%s\n' "Dynamic Forms iniciado em http://localhost:8080"
